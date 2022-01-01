<?php

namespace Addwiki\Mediawiki\Commands;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\MediawikiFactory;
use Addwiki\Mediawiki\DataModel\Page;
use Addwiki\Mediawiki\DataModel\PageIdentifier;
use Addwiki\Mediawiki\DataModel\Title;
use ArrayAccess;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Purge extends Command {

	private ArrayAccess $appConfig;

	public function __construct( ArrayAccess $appConfig ) {
		$this->appConfig = $appConfig;
		parent::__construct( null );
	}

	protected function configure() {
		$defaultWiki = $this->appConfig->offsetGet( 'defaults.wiki' );

		$this
			->setName( 'mw:purge' )
			->setDescription( 'Purges the selected pages' )
			->addOption(
				'wiki',
				null,
				( $defaultWiki === null ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL ),
				'The configured wiki to use',
				$defaultWiki
			)
			->addOption(
				'pageid',
				null,
				InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
				'Which page ids do you want to purge (separate multiple names with a space)?'
			)
			->addOption(
				'title',
				null,
				InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
				'Which titles do you want to purge (separate multiple names with a space)?'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$pageIdentifiers = [];
		foreach ( $input->getOption( 'pageid' ) as $pageId ) {
			$pageIdentifiers[] = new PageIdentifier( null, (int)$pageId );
		}

		foreach ( $input->getOption( 'title' ) as $title ) {
			$pageIdentifiers[] = new PageIdentifier( new Title( $title ) );
		}

		if ( empty( $pageIdentifiers ) ) {
			throw new RuntimeException( 'No titles or pageids were set!' );
		}

		$wiki = $input->getOption( 'wiki' );
		$wikiDetails = $this->appConfig->offsetGet( 'wikis.' . $wiki );
		$api = new ActionApi( $wikiDetails['url'] );
		$mwFactory = new MediawikiFactory( $api );
		$purger = $mwFactory->newPagePurger();
		/** @var PageIdentifier $identifier */
		foreach ( $pageIdentifiers as $identifier ) {
			if ( $identifier->getId() != null ) {
				$output->writeln( 'Purging page with id ' . $identifier->getId() );
			} elseif ( $identifier->getTitle() != null ) {
				$output->writeln( 'Purging page with title ' . $identifier->getTitle()->getText() );
			}

			$purger->purge( new Page( $identifier ) );
		}

		$output->writeln( 'Done' );
		return 0;
	}

}
