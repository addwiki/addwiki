<?php

namespace Addwiki\Wikimedia\Commands;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\Client\Auth\UserAndPassword;
use Addwiki\Mediawiki\Api\MediawikiFactory;
use Addwiki\Mediawiki\DataModel\Content;
use Addwiki\Mediawiki\DataModel\PageIdentifier;
use Addwiki\Mediawiki\DataModel\Revision;
use Addwiki\Mediawiki\DataModel\Title;
use Addwiki\Wikimedia\Api\WikimediaFactory;
use ArrayAccess;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;

class ExtensionToWikidata extends Command {

	private ArrayAccess $appConfig;

	public function __construct( ArrayAccess $appConfig ) {
		$this->appConfig = $appConfig;
		parent::__construct( null );
	}

	protected function configure() {
		$defaultUser = $this->appConfig->offsetGet( 'defaults.user' );

		$this
			->setName( 'wm:exttowd' )
			->setDescription( 'Imports some Extension infomation from mediawiki.org to wikidata' )
			->addOption(
				'user',
				null,
				( $defaultUser === null ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL ),
				'The configured user to use',
				$defaultUser
			)
			->addOption(
				'title',
				null,
				InputOption::VALUE_OPTIONAL,
				'Which title do you want to use (should be an Extension:* page)'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$user = $input->getOption( 'user' );

		$userDetails = $this->appConfig->get( 'users.' . $user );

		if ( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}

		$pageIdentifier = null;
		$titleInputOption = $input->getOption( 'title' );
		if ( $titleInputOption != null ) {
			$sourceTitle = $input->getOption( 'title' );
			$pageIdentifier = new PageIdentifier( new Title( $sourceTitle ) );
		} else {
			throw new RuntimeException( 'No titles was set!' );
		}

		$sourceApi = new ActionApi( "https://www.mediawiki.org/w/api.php" );

		$sourceMwFactory = new MediawikiFactory( $sourceApi );
		$sourceParser = $sourceMwFactory->newParser();
		$parseResult = $sourceParser->parsePage( $pageIdentifier );

		// Get the wikibase item if it exists
		$itemIdString = null;
		if ( array_key_exists( 'properties', $parseResult ) ) {
			foreach ( $parseResult['properties'] as $pageProp ) {
				if ( $pageProp['name'] == 'wikibase_item' ) {
					$itemIdString = $pageProp['*'];
				}
			}
		}

		$targetAuth = new UserAndPassword( $userDetails['username'], $userDetails['password'] );
		$targetWbFactory = ( new WikimediaFactory() )->newWikidataWikibaseFactory( $targetAuth );

		// Create an item if there is no item yet!
		if ( $itemIdString === null ) {
			$output->writeln( "Creating a new Item" );
			$item = new Item();
			$item->setLabel( 'en', $sourceTitle );
			// TODO this siteid should come from somewhere?
			$item->getSiteLinkList()->setNewSiteLink( 'mediawikiwiki', $sourceTitle );
			$targetRevSaver = $targetWbFactory->newRevisionSaver();
			$item = $targetRevSaver->save( new Revision( new Content( $item ) ) );
		} else {
			$item = $targetWbFactory->newItemLookup()->getItemForId( new ItemId( $itemIdString ) );
		}

		// Add instance of if not already there
		$hasInstanceOfExtension = false;
		foreach ( $item->getStatements()->getByPropertyId( new PropertyId( 'P31' ) )->getMainSnaks() as $mainSnak ) {
			if ( $mainSnak instanceof PropertyValueSnak ) {
				/** @var EntityIdValue $dataValue */
				$dataValue = $mainSnak->getDataValue();
				if ( $dataValue->getEntityId()->equals( new ItemId( 'Q6805426' ) ) ) {
					$hasInstanceOfExtension = true;
					break;
				}
			}
		}
		if ( !$hasInstanceOfExtension ) {
			$output->writeln( "Creating instance of Statement" );
			$targetWbFactory->newStatementCreator()->create(
				new PropertyValueSnak(
					new PropertyId( 'P31' ),
					new EntityIdValue( new ItemId( 'Q6805426' ) )
				),
				$item->getId()
			);
		}

		// Try to add a licence
		$catLicenseMap = [
			'Public_domain_licensed_extensions' => 'Q19652',
		];
		$extensionLicenseItemIdString = null;
		if ( array_key_exists( 'categories', $parseResult ) ) {
			foreach ( $parseResult['categories'] as $categoryInfo ) {
				if ( array_key_exists( $categoryInfo['*'], $catLicenseMap ) ) {
					$extensionLicenseItemIdString = $catLicenseMap[$categoryInfo['*']];
				}
			}
		}
		if ( $extensionLicenseItemIdString !== null ) {
			$output->writeln( "Creating Licence Statement" );
			$statementCreator = $targetWbFactory->newStatementCreator();
			// TODO make sure it isn't already there????
			$statementCreator->create(
				new PropertyValueSnak(
					new PropertyId( 'P275' ),
					new EntityIdValue( new ItemId( $extensionLicenseItemIdString ) )
				),
				$item->getId()
			);
		}

		return 0;
	}

}
