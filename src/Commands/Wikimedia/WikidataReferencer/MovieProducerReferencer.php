<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use Mediawiki\Api\UsageException;
use Mediawiki\DataModel\EditInfo;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;

/**
 * @author Addshore
 */
class MovieProducerReferencer implements Referencer {

	/**
	 * @var WikibaseFactory
	 */
	private $wikibaseFactory;

	/**
	 * @var PropertyId
	 */
	private $producerPropertyId;

	public function __construct( WikibaseFactory $wikibaseFactory ) {
		$this->wikibaseFactory = $wikibaseFactory;
		$this->producerPropertyId = new PropertyId( 'P162' );
	}

	public function canAddReferences( MicroData $microData ) {
		return
			$microData->hasType( 'Movie' ) &&
			$microData->hasProperty( 'producer', MicroData::PROP_DATA ) &&
			$microData->getFirstProperty( 'producer', MicroData::PROP_DATA )->hasProperty( 'name', MicroData::PROP_STRING );
	}

	public function addReferences( MicroData $microData, $item, $sourceUrl ) {
		$referenceCounter = 0;

		$producerNames = array();
		foreach( $microData->getProperty( 'producer', MicroData::PROP_DATA ) as $producerMicroData ) {
			foreach( $producerMicroData->getProperty( 'name', MicroData::PROP_STRING ) as $producerName ) {
				$producerNames[] = $producerName;
			}
		}

		$producerStatements = $item->getStatements()->getByPropertyId( $this->producerPropertyId );

		foreach( $producerNames as $name ) {
			foreach ( $producerStatements->getIterator() as &$producerStatement ) {

				$mainSnak = $producerStatement->getMainSnak();
				if ( !$mainSnak instanceof PropertyValueSnak ) {
					continue; // Ignore some and no value statements
				}

				/** @var EntityIdValue $producerEntityIdValue */
				$producerEntityIdValue = $mainSnak->getDataValue();
				/** @var ItemId $producerItemId */
				$producerItemId = $producerEntityIdValue->getEntityId();
				$producerItem = $this->wikibaseFactory->newItemLookup()->getItemForId( $producerItemId );

				if ( !in_array( strtolower( $name ), DataModelUtils::getMainTermsAsLowerCaseStrings( $producerItem->getFingerprint() ) ) ) {
					continue; // Ignore things that don't appear to have the correct value
				}

				if( DataModelUtils::statementHasReferenceForUrlWithSameDomain( $producerStatement, $sourceUrl ) ) {
					continue; // Ignore statements that already have this URL domain as a ref
				}

				// Add the new reference!
				$newReference = DataModelUtils::getReferenceForUrl( $sourceUrl );

				try {
					$this->wikibaseFactory->newReferenceSetter()->set(
						$newReference,
						$producerStatement
					);
					//NOTE: keep our in memory item copy up to date (yay such reference passing)
					$producerStatement->addNewReference( $newReference->getSnaks() );
					$referenceCounter++;
				} catch( UsageException $e ) {
					//Ignore
				}
			}
		}

		return $referenceCounter;
	}

}
