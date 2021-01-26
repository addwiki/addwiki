<?php

namespace Addwiki\Commands\Wikimedia\WikidataReferencer;

use InvalidArgumentException;
use Mediawiki\Api\Guzzle\ClientFactory;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;

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
						"https://$firstPart.wikipedia.org/w/api.php",
						$this->client
					)
				);
			}
		}
		throw new InvalidArgumentException( __CLASS__ . ' cannot create factories for given wikicode' );
	}

}
