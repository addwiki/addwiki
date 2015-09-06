<?php

namespace Mediawiki\Bot\Commands\Task;

use Mediawiki\Bot\Config\AppConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Purge extends Command {

	private $appConfig;

	public function __construct( AppConfig $appConfig ) {
		$this->appConfig = $appConfig;
		parent::__construct( null );
	}

	protected function configure() {
		$defaultWiki = $this->appConfig->get( 'defaults.wiki' );

		$this
			->setName( 'task:restore-revisions' )
			->setDescription( 'Restores the selected revisions' )
			->addOption(
				'wiki',
				null,
				( $defaultWiki === null ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL),
				'The configured wiki to use',
				$defaultWiki
			)
			->addOption(
				'forcelinkupdate',
				null,
				InputOption::VALUE_OPTIONAL,
				'Update the links tables',
				false
			)
			->addOption(
				'forcerecursivelinkupdate',
				null,
				InputOption::VALUE_OPTIONAL,
				'Update the links table, and update the links tables for any page that uses this page as a template',
				false
			)
			->addOption(
				'resolveredirects',
				null,
				InputOption::VALUE_OPTIONAL,
				'Automatically resolve redirects in titles, pageids, and revids',
				false
			)
			->addOption(
				'revid',
				null,
				InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
				'Which revision ids do you want to purge (separate multiple names with a space)?'
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
		//TODO implement me
	}

}
