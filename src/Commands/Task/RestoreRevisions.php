<?php

namespace Mediawiki\Bot\Commands\Task;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
use Mediawiki\Bot\Config\AppConfig;
use Mediawiki\DataModel\Content;
use Mediawiki\DataModel\EditInfo;
use Mediawiki\DataModel\Revision;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreRevisions extends Command {

	private $appConfig;

	public function __construct( AppConfig $appConfig ) {
		parent::__construct( null );
		$this->appConfig = $appConfig;
	}

	protected function configure() {
		$this
			->setName( 'task:restore-revisions' )
			->setDescription( 'Restores the selected revisions' )
			->addArgument(
				'wiki',
				InputArgument::REQUIRED,
				'The configured wiki to use'
			)
			->addArgument(
				'user',
				InputArgument::REQUIRED,
				'The configured user to use'
			)
			->addArgument(
				'revid',
				InputArgument::IS_ARRAY | InputArgument::REQUIRED,
				'Which revision ids do you want to restore (separate multiple names with a space)?'
			)
			->addOption(
				'minor',
				null,
				InputOption::VALUE_OPTIONAL,
				'Mark edits as minor',
				true
			)
			->addOption(
				'bot',
				null,
				InputOption::VALUE_OPTIONAL,
				'Mark edits as bot',
				true
			)->addOption(
				'summary',
				null,
				InputOption::VALUE_OPTIONAL,
				'Override the default edit summary',
				'Restoring page to previous revision: $revid'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$wiki = $input->getArgument( 'wiki' );
		$user = $input->getArgument( 'user' );
		$revids = $input->getArgument( 'revid' );

		$userDetails = $this->appConfig->get( 'users.' . $user );
		$wikiDetails = $this->appConfig->get( 'wikis.' . $wiki );

		if( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}
		if( $wikiDetails === null ) {
			throw new RuntimeException( 'Wiki not found in config' );
		}

		$api = new MediawikiApi( $wikiDetails['url'] );
		$loggedIn = $api->login( new ApiUser( $userDetails['username'], $userDetails['password'] ) );
		if( !$loggedIn ) {
			$output->writeln( 'Failed to log in' );
			return -1;
		}

		$mwFactory = new MediawikiFactory( $api );
		$getter = $mwFactory->newPageGetter();
		$saver = $mwFactory->newRevisionSaver();

		foreach( $revids as $revid ) {
			$revid = intval( $revid );

			$page = $getter->getFromRevisionId( $revid );
			$page = $getter->getFromPage( $page );

			$goodText = $page->getRevisions()->get( $revid )->getContent()->getData();
			$currentRevision = $page->getRevisions()->getLatest();
			$currentText = $currentRevision->getContent()->getData();

			if( $goodText === $currentText ) {
				$output->writeln( 'Page already has same content as revision: ' . $revid );
				return null;
			}

			$newRevision = new Revision( new Content( $goodText ), $page->getPageIdentifier() );
			$success =
				$saver->save(
					$newRevision,
					new EditInfo(
						$this->getEditSummary( $input->getOption( 'summary' ), $revid ),
						$input->getOption( 'minor' ),
						$input->getOption( 'bot' )
					)
				);

			if( $success ) {
				$output->writeln( 'Restored revision: ' . $revid );
			} else {
				$output->writeln( 'Failed to restore revision: ' . $revid );
			}
		}

		return null;
	}

	private function getEditSummary( $rawSummary, $revid ) {
		return str_replace( '$revid', $revid, $rawSummary );
	}

}
