<?php

namespace Addwiki\Cli\Commands\Config;

use Addwiki\Cli\Config\AppConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetDefaultWiki extends Command {

	private $appConfig;

	public function __construct( AppConfig $appConfig ) {
		parent::__construct( null );
		$this->appConfig = $appConfig;
	}

	protected function configure() {
		$this
			->setName( 'config:set:default:wiki' )
			->setDescription( 'Sets the default wiki to be used by scripts' )
			->addArgument(
				'code',
				InputArgument::REQUIRED,
				'The wikicode to set as the default',
				null
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$code = $input->getArgument( 'code' );
		$appConfigHasWiki = $this->appConfig->has( 'wikis.' . $code );

		if ( !$appConfigHasWiki ) {
			$output->writeln( sprintf( 'No wiki with the code %s found', $code ) );
			return -1;
		}

		$this->appConfig->set( 'defaults.wiki', $code );
		$output->writeln( sprintf( 'Default wiki set to: %s', $code ) );
	}
}
