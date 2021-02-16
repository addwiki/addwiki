<?php

namespace Addwiki\Commands\Wikimedia\WikidataReferencer\Referencers;

use Addwiki\Commands\Wikimedia\WikidataReferencer\DataModelUtils;
use Addwiki\Commands\Wikimedia\WikidataReferencer\MicroData\MicroData;
use Mediawiki\Api\UsageException;
use Mediawiki\DataModel\EditInfo;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\EntityIdValue;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;

class MultiTextReferencer implements Referencer {

	/**
	 * @var WikibaseFactory
	 */
	private $wikibaseFactory;

	/**
	 * @var string[]
	 */
	private $propMap = [];

	/**
	 * @var array
	 */
	private $regexMap = [];

	/**
	 * @param WikibaseFactory $wikibaseFactory
	 * @param string[] $propMap of propertyId strings to schema.org properties
	 *          eg. 'P136' => 'genre'
	 * @param array $regexMap of propertyId strings to array of key itemIds and value regexes
	 *          eg. 'P136' => array( 'Q188473' => '/action( ?film)?/i' )
	 */
	public function __construct( WikibaseFactory $wikibaseFactory, array $propMap, array $regexMap ) {
		$this->wikibaseFactory = $wikibaseFactory;
		$this->propMap = $propMap;
		$this->regexMap = $regexMap;
	}

	public function addReferences( MicroData $microData, $item, $sourceUrl ) {
		$referenceCounter = 0;

		foreach ( $this->propMap as $propertyIdString => $schemaPropertyString ) {
			$regexMap = $this->regexMap[$propertyIdString];

			$values = [];
			foreach ( $microData->getProperty( $schemaPropertyString, MicroData::PROP_STRING ) as $propertyValue ) {
				// Don't match URLS!
				if ( strstr( $propertyValue, '//' ) ) {
					continue;
				}
				$values[] = $propertyValue;
			}

			$statements = $item->getStatements()->getByPropertyId( new PropertyId( $propertyIdString ) );

			foreach ( $values as $value ) {
				/** Suppressions can be removed once https://github.com/wmde/WikibaseDataModel/pull/838 is released */
				/** @psalm-suppress UndefinedDocblockClass */
				/** @psalm-suppress UndefinedClass */
				foreach ( $statements->getIterator() as &$statement ) {

					$mainSnak = $statement->getMainSnak();
					if ( !$mainSnak instanceof PropertyValueSnak ) {
						continue; // Ignore some and no value statements
					}

					if ( DataModelUtils::statementHasReferenceForUrlWithSameDomain( $statement, $sourceUrl ) ) {
						continue; // Ignore statements that already have this URL domain as a ref
					}

					/** @var EntityIdValue $valueEntityIdValue */
					$valueEntityIdValue = $mainSnak->getDataValue();
					/** @var EntityId $valueEntityId */
					$valueEntityId = $valueEntityIdValue->getEntityId();
					$valueEntityIdString = $valueEntityId->getSerialization();

					if ( !array_key_exists( $valueEntityIdString, $regexMap ) ) {
						// TODO log that this ItemId is missing?
						continue;
					}

					$regex = $regexMap[$valueEntityIdString];
					if ( !preg_match( $regex, $value ) ) {
						// ItemId regex didn't match this schema value
						continue;
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
						++$referenceCounter;
					}
					catch ( UsageException $usageException ) {
						// Ignore
					}
				}
			}
		}

		return $referenceCounter;
	}

}
