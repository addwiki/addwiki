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
class MovieActorReferencer implements Referencer {

	/**
	 * @var WikibaseFactory
	 */
	private $wikibaseFactory;

	/**
	 * @var PropertyId
	 */
	private $castMemberPropertyId;

	public function __construct( WikibaseFactory $wikibaseFactory ) {
		$this->wikibaseFactory = $wikibaseFactory;
		$this->castMemberPropertyId = new PropertyId( 'P161' );
	}

	public function canAddReferences( MicroData $microData ) {
		return
			$microData->hasType( 'Movie' ) &&
			$microData->hasProperty( 'actor', MicroData::PROP_DATA ) &&
			$microData->getFirstProperty( 'actor', MicroData::PROP_DATA )->hasProperty( 'name', MicroData::PROP_STRING );
	}

	public function addReferences( MicroData $microData, $item, $sourceUrl, $sourceWiki = null ) {
		$referenceCounter = 0;

		$actorNames = array();
		foreach( $microData->getProperty( 'actor', MicroData::PROP_DATA ) as $actorMicroData ) {
			foreach( $actorMicroData->getProperty( 'name', MicroData::PROP_STRING ) as $actorName ) {
				$actorNames[] = $actorName;
			}
		}

		$castMemberStatements = $item->getStatements()->getByPropertyId( $this->castMemberPropertyId );

		foreach( $actorNames as $name ) {
			foreach ( $castMemberStatements->getIterator() as &$actorStatement ) {

				$mainSnak = $actorStatement->getMainSnak();
				if ( !$mainSnak instanceof PropertyValueSnak ) {
					continue; // Ignore some and no value statements
				}

				/** @var EntityIdValue $actorEntityIdValue */
				$actorEntityIdValue = $mainSnak->getDataValue();
				/** @var ItemId $actorItemId */
				$actorItemId = $actorEntityIdValue->getEntityId();
				$actorItem = $this->wikibaseFactory->newItemLookup()->getItemForId( $actorItemId );

				if ( !in_array( strtolower( $name ), DataModelUtils::getMainTermsAsLowerCaseStrings( $actorItem->getFingerprint() ) ) ) {
					continue; // Ignore things that don't appear to have the correct value
				}

				if( DataModelUtils::statementHasReferenceForUrlWithSameDomain( $actorStatement, $sourceUrl ) ) {
					continue; // Ignore statements that already have this URL domain as a ref
				}

				// Add the new reference!
				$newReference = DataModelUtils::getReferenceForUrl( $sourceUrl );
				$editInfo = new EditInfo( "From $sourceWiki with love" );

				try {
					$this->wikibaseFactory->newReferenceSetter()->set(
						$newReference,
						$actorStatement,
						null,
						$editInfo
					);
					//NOTE: keep our in memory item copy up to date (yay such reference passing)
					$actorStatement->addNewReference( $newReference->getSnaks() );
					$referenceCounter++;
				} catch( UsageException $e ) {
					//Ignore
				}
			}
		}

		return $referenceCounter;
	}

}
