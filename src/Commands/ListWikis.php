<?php

namespace Mediawiki\Bot\Commands;

use Mediawiki\Bot\Config\AppConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListWikis extends Command {

	private $appConfig;

	public function __construct( AppConfig $appConfig ) {
		parent::__construct( null );
		$this->appConfig = $appConfig;
	}

	protected function configure() {
		$this
			->setName( 'wikis' )
			->setDescription( 'Lists configured wikis' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$wikis = $this->appConfig->get( 'wikis' );

		if( empty( $wikis ) ) {
			$output->writeln( "You have no wikis configured" );
			return;
		}

		$output->writeln( "You have the following wikis configured:" );
		foreach( $wikis as $wikiCode => $wikiData ) {
			$output->writeln( ' - ' . $wikiCode . ' => ' . $wikiData['url'] );
		}
	}
}