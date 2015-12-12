<?php

namespace Mediawiki\Bot\Commands\Task;

use Mediawiki\Api\ApiUser;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
use Mediawiki\Bot\Config\AppConfig;
use Mediawiki\DataModel\Content;
use Mediawiki\DataModel\Page;
use Mediawiki\DataModel\PageIdentifier;
use Mediawiki\DataModel\Revision;
use Mediawiki\DataModel\Title;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EditPage extends Command {

	private $appConfig;

	public function __construct( AppConfig $appConfig ) {
		$this->appConfig = $appConfig;
		parent::__construct( null );
	}

	protected function configure() {
		$defaultWiki = $this->appConfig->get( 'defaults.wiki' );
		$defaultUser = $this->appConfig->get( 'defaults.user' );

		$this
			->setName( 'task:edit-page' )
			->setDescription( 'Edits the page' )
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
			->addOption(
				'wiki',
				null,
				( $defaultWiki === null ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL),
				'The configured wiki to use',
				$defaultWiki
			)
			->addOption(
				'pageid',
				null,
				InputOption::VALUE_OPTIONAL,
				'Which page id do you want to edit?'
			)
			->addOption(
				'title',
				null,
				InputOption::VALUE_OPTIONAL,
				'Which title do you want to edit?'
			)
			->addArgument(
				'text',
				InputArgument::REQUIRED,
				'Text to be used for the edit'
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$wiki = $input->getOption( 'wiki' );
		$user = $input->getOption( 'user' );

		$userDetails = $this->appConfig->get( 'users.' . $user );
		$wikiDetails = $this->appConfig->get( 'wikis.' . $wiki );

		if( $userDetails === null ) {
			throw new RuntimeException( 'User not found in config' );
		}
		if( $wikiDetails === null ) {
			throw new RuntimeException( 'Wiki not found in config' );
		}

		$pageIdentifier = null;
		if( $input->getOption( 'pageid' ) != null ) {
			$pageIdentifier = new PageIdentifier( null, (int)$input->getOption( 'pageid' ) );
		} elseif( $input->getOption( 'title' ) != null ) {
			$pageIdentifier = new PageIdentifier( new Title( $input->getOption( 'title' ) ) );
		} else {
			throw new \RuntimeException( 'No titles or pageids were set!' );
		}

		$wiki = $input->getOption( 'wiki' );
		$wikiDetails = $this->appConfig->get( 'wikis.' . $wiki );
		$api = new MediawikiApi( $wikiDetails['url'] );
		$loggedIn = $api->login( new ApiUser( $userDetails['username'], $userDetails['password'] ) );
		if( !$loggedIn ) {
			$output->writeln( 'Failed to log in' );
			return -1;
		}

		$mwFactory = new MediawikiFactory( $api );
		$saver = $mwFactory->newRevisionSaver();

		$saver->save( new Revision(
			new Content( $input->getArgument( 'text' ) ),
			$pageIdentifier
		) );

		$output->writeln( 'Done' );
	}

}
