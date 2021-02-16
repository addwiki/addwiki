<?php

namespace Wikimedia\Api;

use DataValues\Deserializers\DataValueDeserializer;
use DataValues\Serializers\DataValueSerializer;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\MediawikiFactory;
use Wikibase\Api\WikibaseFactory;

/**
 * @author Addshore
 * @since 0.1
 */
class WikimediaFactory {

	/**
	 * @since 0.1
	 *
	 * @param string $domain eg. 'en.wikipedia.org'
	 *
	 * @return MediawikiApi
	 */
	public function newMediawikiApiForDomain( $domain ) {
		return MediawikiApi::newFromApiEndpoint( 'https://' . $domain . '/w/api.php' );
	}

	/**
	 * @since 0.1
	 *
	 * @param string $domain eg. 'en.wikipedia.org'
	 *
	 * @return MediawikiFactory
	 */
	public function newMediawikiFactoryForDomain( $domain ) {
		return new MediawikiFactory( $this->newMediawikiApiForDomain( $domain ) );
	}

	/**
	 * @since 0.1
	 *
	 * @param string $domain eg. 'en.wikipedia.org'
	 *
	 * @return WikibaseFactory
	 */
	public function newWikibaseFactoryForDomain( $domain ) {
		switch ( true ) {
			case strstr( $domain, 'wikidata.org' ):
				$dvDeserializer = new DataValueDeserializer(
					[
						// data-values/data-values
						'boolean' => 'DataValues\BooleanValue',
						'number' => 'DataValues\NumberValue',
						'string' => 'DataValues\StringValue',
						'unknown' => 'DataValues\UnknownValue',
						// data-values/geo
						'globecoordinate' => 'DataValues\Geo\Values\GlobeCoordinateValue',
						// data-values/common
						'monolingualtext' => 'DataValues\MonolingualTextValue',
						'multilingualtext' => 'DataValues\MultilingualTextValue',
						// data-values/number
						'quantity' => 'DataValues\QuantityValue',
						// data-values/time
						'time' => 'DataValues\TimeValue',
						// wikibase/data-model
						'wikibase-entityid' => 'Wikibase\DataModel\Entity\EntityIdValue',
					]
				);
				$dvSerializer = new DataValueSerializer();
				break;
			default:
				$dvDeserializer = new DataValueDeserializer();
				$dvSerializer = new DataValueSerializer();
		}

		return new WikibaseFactory(
			$this->newMediawikiApiForDomain( $domain ),
			$dvDeserializer,
			$dvSerializer
		);
	}

}
