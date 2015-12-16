<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use Mediawiki\Api\UsageException;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;

/**
 * @author Addshore
 */
class MoviePersonReferencer implements Referencer {

	/**
	 * @var WikibaseFactory
	 */
	private $wikibaseFactory;

	/**
	 * @var callable[]
	 */
	private $map;

	public function __construct( WikibaseFactory $wikibaseFactory ) {
		$this->wikibaseFactory = $wikibaseFactory;

		$this->map = array(
			'P57' => function( MicroData $microData ) {
				$values = array();
				foreach( $microData->getProperty( 'director', MicroData::PROP_DATA ) as $innerMicrodata ) {
					foreach( $innerMicrodata->getProperty( 'name', MicroData::PROP_STRING ) as $value ) {
						$values[] = $value;
					}
				}
				return $values;
			},
			'P161' => function( MicroData $microData ) {
				$values = array();
				foreach( $microData->getProperty( 'actor', MicroData::PROP_DATA ) as $innerMicrodata ) {
					foreach( $innerMicrodata->getProperty( 'name', MicroData::PROP_STRING ) as $value ) {
						$values[] = $value;
					}
				}
				return $values;
			},
			'P162' => function( MicroData $microData ) {
				$values = array();
				foreach( $microData->getProperty( 'producer', MicroData::PROP_DATA ) as $innerMicrodata ) {
					foreach( $innerMicrodata->getProperty( 'name', MicroData::PROP_STRING ) as $value ) {
						$values[] = $value;
					}
				}
				return $values;
			},
		);
	}

	public function canLookForReferences( MicroData $microData ) {
		return
			$microData->hasType( 'Movie' ) &&
			$microData->hasProperty( 'director', MicroData::PROP_DATA );
	}

	public function addReferences( MicroData $microData, $item, $sourceUrl ) {
		$referenceCounter = 0;

		foreach( $this->map as $propertyIdString => $valueGetterFunction ) {
			$values = $valueGetterFunction( $microData );
			$statements = $item->getStatements()->getByPropertyId( new PropertyId( $propertyIdString ) );

			foreach( $values as $value ) {
				foreach ( $statements->getIterator() as &$statement ) {

					$mainSnak = $statement->getMainSnak();
					if ( !$mainSnak instanceof PropertyValueSnak ) {
						continue; // Ignore some and no value statements
					}

					/** @var EntityIdValue $valueEntityIdValue */
					$valueEntityIdValue = $mainSnak->getDataValue();
					/** @var ItemId $valueItemId */
					$valueItemId = $valueEntityIdValue->getEntityId();
					$valueItem = $this->wikibaseFactory->newItemLookup()->getItemForId( $valueItemId );

					if ( !in_array( strtolower( $value ), DataModelUtils::getMainTermsAsLowerCaseStrings( $valueItem->getFingerprint() ) ) ) {
						continue; // Ignore things that don't appear to have the correct value
					}

					if( DataModelUtils::statementHasReferenceForUrlWithSameDomain( $statement, $sourceUrl ) ) {
						continue; // Ignore statements that already have this URL domain as a ref
					}

					// Add the new reference!
					$newReference = DataModelUtils::getReferenceForUrl( $sourceUrl );

					try {
						$this->wikibaseFactory->newReferenceSetter()->set(
							$newReference,
							$statement
						);
						//NOTE: keep our in memory item copy up to date (yay such reference passing)
						$statement->addNewReference( $newReference->getSnaks() );
						$referenceCounter++;
					} catch( UsageException $e ) {
						//Ignore
					}
				}
			}
		}

		return $referenceCounter;
	}

}
