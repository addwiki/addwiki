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

		$directorNames = array();
		foreach( $microData->getProperty( 'director', MicroData::PROP_DATA ) as $directorMicroData ) {
			foreach( $directorMicroData->getProperty( 'name', MicroData::PROP_STRING ) as $directorName ) {
				$directorNames[] = $directorName;
			}
		}

		$directorStatements = $item->getStatements()->getByPropertyId( $this->directorPropertyId );

		foreach( $directorNames as $name ) {
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

				if ( !in_array( strtolower( $name ), DataModelUtils::getMainTermsAsLowerCaseStrings( $directorItem->getFingerprint() ) ) ) {
					continue; // Ignore things that don't appear to have the correct value
				}

				if( DataModelUtils::statementHasReferenceForUrlWithSameDomain( $directorStatement, $sourceUrl ) ) {
					continue; // Ignore statements that already have this URL domain as a ref
				}

				// Add the new reference!
				$newReference = DataModelUtils::getReferenceForUrl( $sourceUrl );
				$editInfo = new EditInfo( "From $sourceWiki with love" );

				try {
					$this->wikibaseFactory->newReferenceSetter()->set(
						$newReference,
						$directorStatement,
						null,
						$editInfo
					);
					//NOTE: keep our in memory item copy up to date (yay such reference passing)
					$directorStatement->addNewReference( $newReference->getSnaks() );
					$referenceCounter++;
				} catch( UsageException $e ) {
					//Ignore
				}
			}
		}

		return $referenceCounter;
	}

}
