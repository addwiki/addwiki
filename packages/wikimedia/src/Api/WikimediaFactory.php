<?php

namespace Addwiki\Wikimedia\Api;

use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\MediawikiFactory;
use Addwiki\Wikibase\Api\WikibaseFactory;
use DataValues\BooleanValue;
use DataValues\Deserializers\DataValueDeserializer;
use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\MonolingualTextValue;
use DataValues\MultilingualTextValue;
use DataValues\NumberValue;
use DataValues\QuantityValue;
use DataValues\Serializers\DataValueSerializer;
use DataValues\StringValue;
use DataValues\TimeValue;
use DataValues\UnknownValue;
use Wikibase\DataModel\Entity\EntityIdValue;

/**
 * @author Addshore
 * @since 0.1
 */
class WikimediaFactory {

	/**
	 * @param string $domain eg. 'en.wikipedia.org'
	 *
	 * @return \Addwiki\Mediawiki\Api\Client\MediawikiApi
	 * @since 0.1
	 *
	 */
	public function newMediawikiApiForDomain( $domain ) {
		return \Addwiki\Mediawiki\Api\Client\MediawikiApi::newFromApiEndpoint( 'https://' . $domain . '/w/api.php' );
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
		if ( strstr( $domain, 'wikidata.org' ) == true ) {
			$dvDeserializer = new DataValueDeserializer(
					[
						// data-values/data-values
						'boolean' => BooleanValue::class,
						'number' => NumberValue::class,
						'string' => StringValue::class,
						'unknown' => UnknownValue::class,
						// data-values/geo
						'globecoordinate' => GlobeCoordinateValue::class,
						// data-values/common
						'monolingualtext' => MonolingualTextValue::class,
						'multilingualtext' => MultilingualTextValue::class,
						// data-values/number
						'quantity' => QuantityValue::class,
						// data-values/time
						'time' => TimeValue::class,
						// wikibase/data-model
						'wikibase-entityid' => EntityIdValue::class,
					]
				);
			$dvSerializer = new DataValueSerializer();
		} else {
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
