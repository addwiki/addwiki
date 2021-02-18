<?php

namespace Addwiki\Cli\Commands\Config;

use Addwiki\Cli\Config\AppConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigList extends Command {

	private $appConfig;

	public function __construct( AppConfig $appConfig ) {
		parent::__construct( null );
		$this->appConfig = $appConfig;
	}

	protected function configure() {
		$this
			->setName( 'config:list' )
			->setDescription( 'Lists items stored in the config' )
			->addArgument(
				'items',
				InputArgument::OPTIONAL,
				'The items to show (wikis, users)',
				null
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$items = $input->getArgument( 'items' );

		if ( $items === 'wikis' || $items === null ) {
			$wikis = $this->appConfig->get( 'wikis' );

			if ( empty( $wikis ) ) {
				$output->writeln( "You have no wikis configured" );
				return null;
			}

			$output->writeln( "You have the following wikis configured:" );
			foreach ( $wikis as $wikiCode => $wikiData ) {
				$output->writeln( ' - ' . $wikiCode . ' => ' . $wikiData['url'] );
			}
		}

		if ( $items === 'users' || $items === null ) {
			$users = $this->appConfig->get( 'users' );

			if ( empty( $users ) ) {
				$output->writeln( "You have no users configured" );
				return null;
			}

			$output->writeln( "You have the following users configured:" );
			foreach ( $users as $userCode => $userData ) {
				$output->writeln( ' - ' . $userCode . ' => ' . $userData['username'] . ' (password hidden)' );
			}
		}
	}
}
