<?php

namespace Addwiki\Commands\Mediawiki;

use ArrayAccess;
use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
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

	public function __construct( ArrayAccess $appConfig ) {
		$this->appConfig = $appConfig;
		parent::__construct( null );
	}

	protected function configure() {
		$defaultWiki = $this->appConfig->offsetGet( 'defaults.wiki' );
		$defaultUser = $this->appConfig->offsetGet( 'defaults.user' );

		$this
			->setName( 'mw:restore-revisions' )
			->setDescription( 'Restores the selected revisions of pages as their current revision' )
			->addOption(
				'wiki',
				null,
				( $defaultWiki === null ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL),
				'The configured wiki to use',
				$defaultWiki
			)
			->addOption(
				'user',
				null,
				( $defaultUser === null ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL),
				'The configured user to use',
				$defaultUser
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
			)->addOption(
				'asheader',
				null,
				InputOption::VALUE_OPTIONAL,
				'Only restore the revision as the page header',
				false
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$wiki = $input->getOption( 'wiki' );
		$user = $input->getOption( 'user' );
		$revids = $input->getArgument( 'revid' );

		$userDetails = $this->appConfig->offsetGet( 'users.' . $user );
		$wikiDetails = $this->appConfig->offsetGet( 'wikis.' . $wiki );

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

			if( $input->getOption( 'asheader' ) ) {
				if( strstr( $currentText, $goodText ) ) {
					$goodText = $goodText . "\n\n" . trim( str_replace( $goodText, '', $currentText ) );
				} else {
					$goodText = $goodText . "\n\n" . $currentText;
				}
			}

			$newRevision = new Revision( new Content( $goodText ), $page->getPageIdentifier() );
			$success =
				$saver->save(
					$newRevision,
					new EditInfo(
						$this->getEditSummary( $input->getOption( 'summary' ), $revid ),
						boolval( $input->getOption( 'minor' ) ),
						boolval( $input->getOption( 'bot' ) )
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
