<?php

namespace Addwiki\Mediawiki\Commands;

use Addwiki\Mediawiki\Api\Client\Auth\UserAndPassword;
use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\MediawikiFactory;
use Addwiki\Mediawiki\DataModel\Content;
use Addwiki\Mediawiki\DataModel\EditInfo;
use Addwiki\Mediawiki\DataModel\Revision;
use ArrayAccess;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreRevisions extends Command {

	private ArrayAccess $appConfig;

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
				( $defaultWiki === null ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL ),
				'The configured wiki to use',
				$defaultWiki
			)
			->addOption(
				'user',
				null,
				( $defaultUser === null ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL ),
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
				false
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

		if ( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}
		if ( $wikiDetails === null ) {
			throw new RuntimeException( 'Wiki not found in config' );
		}

		$api = new MediawikiApi( $wikiDetails['url'], new UserAndPassword( $userDetails['username'], $userDetails['password'] ) );

		$mwFactory = new MediawikiFactory( $api );
		$getter = $mwFactory->newPageGetter();
		$saver = $mwFactory->newRevisionSaver();

		foreach ( $revids as $revid ) {
			$revid = (int)$revid;

			$page = $getter->getFromRevisionId( $revid );
			$page = $getter->getFromPage( $page );

			$goodText = $page->getRevisions()->get( $revid )->getContent()->getData();
			$currentRevision = $page->getRevisions()->getLatest();
			$currentText = $currentRevision->getContent()->getData();

			if ( $goodText === $currentText ) {
				$output->writeln( 'Page already has same content as revision: ' . $revid );
				return 0;
			}
			$asheaderInputOption = $input->getOption( 'asheader' );

			if ( $asheaderInputOption ) {
				if ( strstr( $currentText, (string)$goodText ) ) {
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

			if ( $success ) {
				$output->writeln( 'Restored revision: ' . $revid );
			} else {
				$output->writeln( 'Failed to restore revision: ' . $revid );
			}
		}

		return 0;
	}

	/**
	 * @return mixed[]|string
	 */
	private function getEditSummary( $rawSummary, $revid ) {
		return str_replace( '$revid', $revid, $rawSummary );
	}

}
