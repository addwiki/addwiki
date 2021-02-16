<?php

namespace Addwiki\Commands\Wikimedia\WikidataReferencer;

use DataValues\StringValue;
use DataValues\TimeValue;
use Exception;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\SiteLinkList;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Term\Fingerprint;

/**
 * @author Addshore
 */
class DataModelUtils {

	/**
	 * @param string $url
	 *
	 * @return Reference
	 */
	public static function getReferenceForUrl( $url ) {
		return new Reference(
			[
				// Reference URL
				new PropertyValueSnak( new PropertyId( 'P854' ), new StringValue( $url ) ),
				// Date retrieved
				new PropertyValueSnak( new PropertyId( 'P813' ), self::getCurrentTimeValue() )
				// TODO date published?
			]
		);
	}

	public static function getCurrentTimeValue() {
		return new TimeValue(
			"+" . date( 'Y-m-d' ) . "T00:00:00Z",
			0, // TODO don't assume UTC
			0,
			0,
			TimeValue::PRECISION_DAY,
			TimeValue::CALENDAR_GREGORIAN
		);
	}

	/**
	 * @param Fingerprint $fingerprint
	 *
	 * @return string[]
	 */
	public static function getMainTermsAsLowerCaseStrings( Fingerprint $fingerprint ) {
		$strings = [];
		$langsToUse = [ 'en', 'en-gb' ];
		foreach ( $langsToUse as $lang ) {
			try{
				$strings[] = $fingerprint->getLabel( $lang )->getText();
			} catch ( Exception $exception ) {

			}
			try{
				$strings = array_merge( $strings, $fingerprint->getAliasGroup( $lang )->getAliases() );
			} catch ( Exception $e ) {
				// Ignore!
			}
		}
		return array_map( 'strtolower', $strings );
	}

	/**
	 * @param Statement $statement
	 * @param string $url
	 *
	 * @return bool
	 */
	public static function statementHasReferenceForUrlWithSameDomain( Statement $statement, $url ) {
		$currentReferences = $statement->getReferences();
		foreach ( $currentReferences as $currentReference ) {
			foreach ( $currentReference->getSnaks() as $currentReferenceSnak ) {
				if ( !$currentReferenceSnak instanceof PropertyValueSnak ) {
					continue; // Ignore some and no value snaks
				}

				// Note: P854 is reference URL
				if ( $currentReferenceSnak->getPropertyId()->getSerialization() == 'P854' ) {
					/** @var StringValue $currentReferenceValue */
					$currentReferenceValue = $currentReferenceSnak->getDataValue();
					$currentReferenceUrl = $currentReferenceValue->getValue();
					if ( self::urlsDomainsAreSame( $currentReferenceUrl, $url ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * @param string $a a URL
	 * @param string $b a URL
	 *
	 * @return bool
	 */
	private static function urlsDomainsAreSame( $a, $b ) {
		return parse_url( $a, PHP_URL_HOST ) === parse_url( $b, PHP_URL_HOST );
	}

	/**
	 * @param SiteLinkList $siteLinkList
	 * @param string $suffix
	 *
	 * @return SiteLinkList
	 */
	public static function getSitelinksWiteSiteIdSuffix( SiteLinkList $siteLinkList, $suffix ) {
		$filteredSiteLinkList = new SiteLinkList();

		/** Suppressions can be removed once https://github.com/wmde/WikibaseDataModel/pull/838 is released */
		/** @psalm-suppress UndefinedDocblockClass */
		/** @psalm-suppress UndefinedClass */
		foreach ( $siteLinkList->getIterator() as $siteLink ) {
			$siteId = $siteLink->getSiteId();
			if ( substr( $siteId, -strlen( $suffix ) ) === $suffix ) {
				$filteredSiteLinkList->addSiteLink( $siteLink );
			}
		}

		return $filteredSiteLinkList;
	}

}
