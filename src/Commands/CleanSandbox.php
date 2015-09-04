<?php

namespace Mediawiki\Bot\Commands;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
use Mediawiki\Bot\Config\AppConfig;
use Mediawiki\DataModel\Content;
use Mediawiki\DataModel\EditInfo;
use Mediawiki\DataModel\Revision;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanSandbox extends Command {

	private $appConfig;

	public function __construct( AppConfig $appConfig ) {
		parent::__construct( null );
		$this->appConfig = $appConfig;
	}

	protected function configure() {
		$this
			->setName( 'cleansandbox' )
			->setDescription( 'Cleans a sandbox....?' )
			->addArgument(
				'wiki',
				InputArgument::REQUIRED
			)->addArgument(
				'user',
				InputArgument::REQUIRED
			)->addArgument(
				'revid',
				InputArgument::REQUIRED
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$wiki = $input->getArgument( 'wiki' );
		$user = $input->getArgument( 'user' );
		$revid = intval( $input->getArgument( 'revid' ) );

		$userDetails = $this->appConfig->get( 'users.' . $user );
		$wikiDetails = $this->appConfig->get( 'wikis.' . $wiki );

		$api = new MediawikiApi( $wikiDetails['url'] );
		$loggedIn = $api->login( new ApiUser( $userDetails['username'], $userDetails['password'] ) );
		if( !$loggedIn ) {
			die( "something went wrong" );
		}

		$mwFactory = new MediawikiFactory( $api );
		$getter = $mwFactory->newPageGetter();
		$saver = $mwFactory->newRevisionSaver();

		$page = $getter->getFromRevisionId( $revid );
		$page = $getter->getFromPage( $page );

		$goodText = $page->getRevisions()->get( $revid )->getContent()->getData();
		$currentRevision = $page->getRevisions()->getLatest();
		$currentText = $currentRevision->getContent()->getData();

		if( $goodText === $currentText ) {
			return 'Sandbox already clear';
		}

		if( strstr( $currentText, $goodText ) ) {
			$newText = str_replace( $goodText, '', $currentText );
			//todo trim newlines from the top of new
			$newText = $goodText . "\n\n" . $newText;
		} else {
			$newText = $goodText;
		}

		$newRevision = new Revision( new Content( $newText ), $page->getPageIdentifier() );
		$success = $saver->save( $newRevision, new EditInfo( "Restoring page to revision $revid", EditInfo::MINOR, EditInfo::BOT ) );

		if( $success ) {
			return 'Cleared Sandbox';
		} else {
			return 'Failed to clear sandbox';
		}
	}

}
