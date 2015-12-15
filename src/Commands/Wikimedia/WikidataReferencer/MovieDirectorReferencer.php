<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use DataValues\StringValue;
use DataValues\TimeValue;
use Exception;
use Mediawiki\Api\UsageException;
use Mediawiki\DataModel\EditInfo;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikibase\DataModel\Term\Fingerprint;

class MovieDirectorReferencer implements Referencer {

	/**
	 * @var WikibaseFactory
	 */
	private $wikibaseFactory;

	/**
	 * @var PropertyId
	 */
	private $directorPropertyId;

	public function __construct( WikibaseFactory $wikibaseFactory ) {
		$this->wikibaseFactory = $wikibaseFactory;
		$this->directorPropertyId = new PropertyId( 'P57' );
	}

	public function canAddReferences( MicroData $microData ) {
		return
			$microData->hasType( 'Movie' ) &&
			$microData->hasProperty( 'director', MicroData::PROP_DATA ) &&
			$microData->getFirstProperty( 'director', MicroData::PROP_DATA )->hasProperty( 'name', MicroData::PROP_STRING );
	}

	public function addReferences( MicroData $microData, $item, $sourceUrl, $sourceWiki = null ) {
		$referenceCounter = 0;
		foreach( $microData->getProperty( 'director', MicroData::PROP_DATA ) as $directorMicroData ) {
			foreach( $directorMicroData->getProperty( 'name', MicroData::PROP_STRING ) as $directorName ) {
				$addedRefs = $this->addReferencesForDirector(
					trim( $directorName ),
					$item,
					$sourceUrl,
					$sourceWiki
				);
				$referenceCounter = $referenceCounter + $addedRefs;
			}
		}

		return $referenceCounter;
	}

	/**
	 * @param string $directorName
	 * @param Item $item
	 * @param string $sourceUrl
	 * @param string|null $sourceWiki
	 *
	 * @return int number of references added
	 */
	private function addReferencesForDirector(
		$directorName,
		$item,
		$sourceUrl,
		$sourceWiki = null
	) {
		$referenceCounter = 0;

		$directorStatements = $item->getStatements()->getByPropertyId( $this->directorPropertyId );
		foreach ( $directorStatements->getIterator() as &$directorStatement ) {

			$mainSnak = $directorStatement->getMainSnak();
			if ( !$mainSnak instanceof PropertyValueSnak ) {
				continue; // Ignore some and no value statements
			}

			/** @var EntityIdValue $directorEntityIdValue */
			$directorEntityIdValue = $mainSnak->getDataValue();
			/** @var ItemId $directorItemId */
			$directorItemId = $directorEntityIdValue->getEntityId();
			$directorItem = $this->wikibaseFactory->newItemLookup()->getItemForId( $directorItemId );

			$englishTerms =
				array_map(
					'strtolower',
					$this->getTermsAsStrings( $directorItem->getFingerprint() )
				);
			if ( !in_array( strtolower( $directorName ), $englishTerms ) ) {
				continue; // Ignore things that dont appear to have the correct value
			}

			/** @var Reference[] $currentReferences */
			$currentReferences = $directorStatement->getReferences();
			foreach ( $currentReferences as $currentReference ) {
				foreach ( $currentReference->getSnaks() as $currentReferenceSnak ) {
					if ( !$currentReferenceSnak instanceof PropertyValueSnak ) {
						continue; // Ignore some and no value snaks
					}

					//Note: P854 is reference URL
					if ( $currentReferenceSnak->getPropertyId()->getSerialization() == 'P854' ) {
						/** @var StringValue $currentReferenceValue */
						$currentReferenceValue = $currentReferenceSnak->getDataValue();
						$currentReferenceUrl = $currentReferenceValue->getValue();
						if ( $this->urlsAreSame( $currentReferenceUrl, $sourceUrl ) ) {
							continue; // Ignore statements that already look like they have this reference URL
						}
					}
				}
			}

			// Add the new reference!
			$newRef = new Reference(
				array(
					// Refernce URL
					new PropertyValueSnak( new PropertyId( 'P854' ), new StringValue( $sourceUrl ) ),
					// Date retrieved
					new PropertyValueSnak( new PropertyId( 'P813' ), $this->getWikidataNowTimeValue() )
					// TODO date published?
				)
			);

			$editInfo = new EditInfo( "From $sourceWiki with love" );

			try {
				$this->wikibaseFactory->newReferenceSetter()->set(
					$newRef,
					$directorStatement,
					null,
					$editInfo
				);
				//NOTE: keep our in memory item copy up to date (yay such reference passing)
				$directorStatement->addNewReference( $newRef->getSnaks() );
				$referenceCounter++;
			} catch( UsageException $e ) {
				//Ignore
			}
		}

		return $referenceCounter;
	}

	/**
	 * @param Fingerprint $fingerprint
	 *
	 * @return string[]
	 */
	private function getTermsAsStrings( Fingerprint $fingerprint ) {
		$strings = array();
		$englishLangs = array( 'en', 'en-gb' );
		foreach( $englishLangs as $lang ) {
			try{
				$strings[] = $fingerprint->getLabel( $lang )->getText();
			} catch ( Exception $e ) {
				// Ignore!
			}
			try{
				$strings = array_merge( $strings, $fingerprint->getAliasGroup( $lang )->getAliases() );
			} catch ( Exception $e ) {
				// Ignore!
			}
		}
		return $strings;

	}

	private function getWikidataNowTimeValue() {
		return new TimeValue(
			"+" . date( 'Y-m-d' ) . "T00:00:00Z",
			0,//TODO dont assume UTC
			0,
			0,
			TimeValue::PRECISION_DAY,
			TimeValue::CALENDAR_JULIAN
		);
	}

	/**
	 * @todo improve this comparison...
	 *
	 * @param string $a
	 * @param string $b
	 *
	 * @return bool
	 */
	private function urlsAreSame( $a, $b ) {
		$regex = '#^https?://#';
		$a = preg_replace($regex, '', $a);
		$b = preg_replace($regex, '', $b);
		$a = trim( $a, "/" );
		$b = trim( $b, "/" );
		return $a == $b;
	}

}
