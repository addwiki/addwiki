<?php

namespace Addwiki\Commands\Config;

use Addwiki\Config\AppConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetDefaultUser extends Command {

	private $appConfig;

	public function __construct( AppConfig $appConfig ) {
		parent::__construct( null );
		$this->appConfig = $appConfig;
	}

	protected function configure() {
		$this
			->setName( 'config:set:default:user' )
			->setDescription( 'Sets the default user to be used by scripts' )
			->addArgument(
				'code',
				InputArgument::REQUIRED,
				'The usercode to set as the default',
				null
			);
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$code = $input->getArgument( 'code' );

		if ( !$this->appConfig->has( 'users.' . $code ) ) {
			$output->writeln( "No user with the code $code found" );
			return -1;
		}

		$this->appConfig->set( 'defaults.user', $code );
		$output->writeln( "Default user set to: $code" );
	}
}
