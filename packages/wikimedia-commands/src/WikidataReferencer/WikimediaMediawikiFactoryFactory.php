<?php

namespace Addwiki\Wikimedia\Commands\WikidataReferencer;

use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\Guzzle\ClientFactory;
use Addwiki\Mediawiki\Api\MediawikiFactory;
use InvalidArgumentException;

/**
 * @author Addshore
 */
class WikimediaMediawikiFactoryFactory {

	private $client;

	public function __construct( ClientFactory $clientFactory ) {
		$this->client = $clientFactory->getClient();
	}

	/**
	 * @todo this could be in a lib? Also this needs more sites adding to it!
	 *
	 * @param string $siteID
	 *
	 * @return MediawikiFactory
	 */
	public function getFactory( $siteID ) {
		$lastFour = substr( $siteID, -4 );
		if ( $lastFour == 'wiki' ) {
			$firstPart = substr( $siteID, 0, -4 );
			if ( strlen( $firstPart ) >= 2 ) {
				$firstPart = str_replace( '_', '-', $firstPart );
				return new MediawikiFactory(
					new MediawikiApi(
						sprintf( 'https://%s.wikipedia.org/w/api.php', $firstPart ),
						$this->client
					)
				);
			}
		}
		throw new InvalidArgumentException( __CLASS__ . ' cannot create factories for given wikicode' );
	}

}
