<?php

namespace Addwiki\Wikimedia\Api;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\Client\Auth\AuthMethod;
use Addwiki\Mediawiki\Api\MediawikiFactory;
use Addwiki\Wikibase\Api\WikibaseFactory;
use Addwiki\Wikibase\DataModel\DataModelFactory;
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

class WikimediaFactory {

	/**
	 * @param string $domain eg. 'en.wikipedia.org'
	 * @param AuthMethod|null $auth
	 */
	public function newMediawikiApiForDomain( string $domain, AuthMethod $auth = null ): ActionApi {
		return new ActionApi( 'https://' . $domain . '/w/api.php', $auth );
	}

	/**
	 * @param string $domain eg. 'en.wikipedia.org'
	 * @param AuthMethod|null $auth
	 */
	public function newMediawikiFactoryForDomain( string $domain, AuthMethod $auth = null ): MediawikiFactory {
		return new MediawikiFactory( $this->newMediawikiApiForDomain( $domain, $auth ) );
	}

	private function domainFromURL( string $url ): string {
		$parsed = parse_url( $url );
		return $parsed['host'];
	}

	/**
	 * @param string $url eg.'https://en.wikipedia.org/w/api.php'
	 * @param AuthMethod|null $auth
	 */
	public function newMediawikiApiForURL( string $url, AuthMethod $auth = null ): ActionApi {
		return $this->newMediawikiApiForDomain( $this->domainFromURL( $url ), $auth );
	}

	/**
	 * @param string $url eg.'https://www.wikidata.org/w/api.php'
	 * @param AuthMethod|null $auth
	 */
	public function newWikibaseFactoryForURL( string $url, AuthMethod $auth = null ): WikibaseFactory {
		return $this->newWikibaseFactoryForDomain( $this->domainFromURL( $url ), $auth );
	}

	/**
	 * @param string $domain eg. 'wikidata.org'
	 * @param AuthMethod|null $auth
	 */
	public function newWikibaseFactoryForDomain( string $domain, AuthMethod $auth = null ): WikibaseFactory {
		// Assume that all Wikibases have the same data values deployed...
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

		return new WikibaseFactory(
			$this->newMediawikiApiForDomain( $domain, $auth ),
			new DataModelFactory(
				$dvDeserializer,
				$dvSerializer
			)
		);
	}

	public function newWikidataWikibaseFactory( AuthMethod $auth = null ): WikibaseFactory {
		return $this->newWikibaseFactoryForDomain( 'wikidata.org', $auth );
	}

	public function newCommonsWikibaseFactory( AuthMethod $auth = null ): WikibaseFactory {
		return $this->newWikibaseFactoryForDomain( 'commons.wikimedia.org', $auth );
	}

}
