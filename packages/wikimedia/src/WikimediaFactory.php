<?php

namespace Wikimedia\Api;

use DataValues\BooleanValue;
use DataValues\NumberValue;
use DataValues\StringValue;
use DataValues\UnknownValue;
use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\MonolingualTextValue;
use DataValues\MultilingualTextValue;
use DataValues\QuantityValue;
use DataValues\TimeValue;
use Wikibase\DataModel\Entity\EntityIdValue;
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
		if (true == strstr( $domain, 'wikidata.org' )) {
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
