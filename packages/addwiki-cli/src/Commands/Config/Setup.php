<?php

namespace Addwiki\Cli\Commands\Config;

use Addwiki\Cli\Config\AppConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class Setup extends Command {

	private $appConfig;

	public function __construct( AppConfig $appConfig ) {
		parent::__construct( null );
		$this->appConfig = $appConfig;
	}

	protected function configure() {
		$this
			->setName( 'config:setup' )
			->setDescription( 'Easy setup of the application' );
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$questionHelper = $this->getQuestionHelper();

		// Add wikis?
		$addWikiQuestion =
			new ConfirmationQuestion( 'Would you like to add a wiki api endpoint? ', false );
		while ( $questionHelper->ask( $input, $output, $addWikiQuestion ) ) {
			$question = new Question( 'Please enter a code for this wiki: ' );
			$code = $questionHelper->ask( $input, $output, $question );
			$appConfigHasWiki = $this->appConfig->has( 'wikis.' . $code );
			if ( $appConfigHasWiki ) {
				$question =
					new ConfirmationQuestion(
						'A wiki with that code already exists, would you like to overwrite it? ',
						false
					);
				$questionHelperAsk = $questionHelper->ask( $input, $output, $question );
				if ( !$questionHelperAsk ) {
					continue 1;
				}
			}

			$question = new Question( 'Please enter a wiki api endpoint: ' );
			$url = $questionHelper->ask( $input, $output, $question );

			$output->writeln( sprintf( '%s, %s', $code, $url ) );
			$question = new ConfirmationQuestion( 'Do these details look correct? ', false );
			$questionHelperAsk = $questionHelper->ask( $input, $output, $question );
			if ( $questionHelperAsk ) {
				$this->appConfig->set(
					'wikis.' . $code,
					[ 'url' => $url ]
				);
				$output->writeln( "Written to the config!" );
			}
		}

		// Add users?
		$addUserQuestion = new ConfirmationQuestion( 'Would you like to add a user? ', false );
		while ( $questionHelper->ask( $input, $output, $addUserQuestion ) ) {
			$question = new Question( 'Please enter a username: ' );
			$username = $questionHelper->ask( $input, $output, $question );

			// TODO oauth? :D
			$question = new Question( 'Please enter a password: ' );
			$question->setHidden( true );
			$password = $questionHelper->ask( $input, $output, $question );

			$question = new ConfirmationQuestion( 'Do you want to save this user? ', false );
			$questionHelperAsk = $questionHelper->ask( $input, $output, $question );
			if ( $questionHelperAsk ) {
				$this->appConfig->set(
					'users.' . $username,
					[ 'username' => $username, 'password' => $password ]
				);
				$output->writeln( "Written to the config!" );
			}
		}

		$output->writeln( "Setup complete" );
	}

	/**
	 * @return QuestionHelper
	 */
	private function getQuestionHelper() {
		return $this->getHelper( 'question' );
	}
}
