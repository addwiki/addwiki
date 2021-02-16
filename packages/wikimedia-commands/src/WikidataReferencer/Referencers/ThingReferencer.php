<?php

namespace Addwiki\Commands\Wikimedia\WikidataReferencer\Referencers;

use Addwiki\Commands\Wikimedia\WikidataReferencer\DataModelUtils;
use Addwiki\Commands\Wikimedia\WikidataReferencer\MicroData\MicroData;
use Mediawiki\Api\UsageException;
use Mediawiki\DataModel\EditInfo;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Services\Lookup\InMemoryEntityLookup;
use Wikibase\DataModel\Snak\PropertyValueSnak;

/**
 * @author Addshore
 */
class ThingReferencer implements Referencer {

	/**
	 * @var WikibaseFactory
	 */
	private $wikibaseFactory;

	/**
	 * @var callable[]
	 */
	private $callbackMap = [];

	/**
	 * @var InMemoryEntityLookup
	 */
	private $inMemoryEntityLookup;

	/**
	 * @var EntityId
	 */
	private $lastEntityId;

	/**
	 * @param WikibaseFactory $wikibaseFactory
	 * @param string[] $propMap of propertyId strings to schema.org properties
	 *          eg. 'P57' => 'director'
	 */
	public function __construct( WikibaseFactory $wikibaseFactory, array $propMap ) {
		$this->wikibaseFactory = $wikibaseFactory;
		$this->inMemoryEntityLookup = new InMemoryEntityLookup();

		foreach ( $propMap as $propertyIdSerialization => $schemaPropertyStrings ) {
			if ( is_string( $schemaPropertyStrings ) ) {
				$schemaPropertyStrings = [ $schemaPropertyStrings ];
			}
			foreach ( $schemaPropertyStrings as $schemaPropertyString ) {
				$this->callbackMap[$propertyIdSerialization] = function ( MicroData $microData ) use ( $schemaPropertyString ) {
					$values = [];
					foreach ( $microData->getProperty( $schemaPropertyString, MicroData::PROP_DATA ) as $innerMicrodata ) {
						foreach ( $innerMicrodata->getProperty( 'name', MicroData::PROP_STRING ) as $value ) {
							$values[] = $value;
						}
					}
					return $values;
				};
			}
		}
	}

	public function addReferences( MicroData $microData, $item, $sourceUrl ) {
		// Only cache entity lookup stuff per item we are adding references for!
		// (but can be used for multiple sourceURLs!!
		if ( !$item->getId()->equals( $this->lastEntityId ) ) {
			$this->inMemoryEntityLookup = new InMemoryEntityLookup();
		}

		$referenceCounter = 0;

		foreach ( $this->callbackMap as $propertyIdString => $valueGetterFunction ) {
			$values = $valueGetterFunction( $microData );
			$statements = $item->getStatements()->getByPropertyId( new PropertyId( $propertyIdString ) );

			foreach ( $values as $value ) {
				foreach ( $statements->getIterator() as &$statement ) {

					$mainSnak = $statement->getMainSnak();
					if ( !$mainSnak instanceof PropertyValueSnak ) {
						continue; // Ignore some and no value statements
					}

					/** @var EntityIdValue $valueEntityIdValue */
					$valueEntityIdValue = $mainSnak->getDataValue();
					/** @var ItemId $valueItemId */
					$valueItemId = $valueEntityIdValue->getEntityId();

					if ( $this->inMemoryEntityLookup->hasEntity( $valueItemId ) ) {
						$valueItem = $this->inMemoryEntityLookup->getEntity( $valueItemId );
					} else {
						$valueItem = $this->wikibaseFactory->newItemLookup()->getItemForId( $valueItemId );
						$this->inMemoryEntityLookup->addEntity( $valueItem );
					}

					if ( !in_array( strtolower( $value ), DataModelUtils::getMainTermsAsLowerCaseStrings( $valueItem->getFingerprint() ) ) ) {
						continue; // Ignore things that don't appear to have the correct value
					}

					if ( DataModelUtils::statementHasReferenceForUrlWithSameDomain( $statement, $sourceUrl ) ) {
						continue; // Ignore statements that already have this URL domain as a ref
					}

					// Add the new reference!
					$newReference = DataModelUtils::getReferenceForUrl( $sourceUrl );

					try {
						$this->wikibaseFactory->newReferenceSetter()->set(
							$newReference,
							$statement,
							null,
							new EditInfo( urldecode( $sourceUrl ), EditInfo::NOTMINOR, EditInfo::BOT )
						);
						// NOTE: keep our in memory item copy up to date (yay such reference passing)
						$statement->addNewReference( $newReference->getSnaks() );
						$referenceCounter++;
					} catch ( UsageException $e ) {
						// Ignore
					}
				}
			}
		}

		return $referenceCounter;
	}

}
