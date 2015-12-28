<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use DataValues\TimeValue;
use DateTime;
use Exception;
use Mediawiki\Api\UsageException;
use ValueParsers\EraParser;
use ValueParsers\IsoTimestampParser;
use ValueParsers\MonthNameUnlocalizer;
use ValueParsers\PhpDateTimeParser;
use Wikibase\Api\WikibaseFactory;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;

class DateReferencer implements Referencer {

	/**
	 * @var WikibaseFactory
	 */
	private $wikibaseFactory;

	/**
	 * @var string[]
	 */
	private $propMap = array();

	/**
	 * @var PhpDateTimeParser
	 */
	private $timeParser;

	/**
	 * @param WikibaseFactory $wikibaseFactory
	 * @param string[] $propMap of propertyId strings to schema.org properties
	 *          eg. 'P577' => 'datePublished'
	 */
	public function __construct( WikibaseFactory $wikibaseFactory, array $propMap ) {
		$this->wikibaseFactory = $wikibaseFactory;
		$this->propMap = $propMap;
		$this->timeParser = new PhpDateTimeParser(
			new MonthNameUnlocalizer( array() ),
			new EraParser(),
			new IsoTimestampParser()
		);
	}

	public function addReferences( MicroData $microData, $item, $sourceUrl ) {
		$referenceCounter = 0;

		foreach ( $this->propMap as $propertyIdString => $schemaPropertyString ) {
			/** @var TimeValue[] $timeValues */
			$timeValues = array();
			foreach( $microData->getProperty( $schemaPropertyString, MicroData::PROP_STRING ) as $propertyValue ) {
				try{
					$date =  new DateTime( trim( $propertyValue ) );
					$timeValues[] = $this->timeParser->parse( $date->format( 'Y m d' ) );
				} catch( Exception $e ) {
					// Ignore failed parsing
				}
			}

			$statements = $item->getStatements()->getByPropertyId( new PropertyId( $propertyIdString ) );

			foreach ( $timeValues as $timeValue ) {
				foreach ( $statements->getIterator() as &$statement ) {

					$mainSnak = $statement->getMainSnak();
					if ( !$mainSnak instanceof PropertyValueSnak ) {
						continue; // Ignore some and no value statements
					}

					if ( DataModelUtils::statementHasReferenceForUrlWithSameDomain( $statement, $sourceUrl ) ) {
						continue; // Ignore statements that already have this URL domain as a ref
					}

					if( !$timeValue->equals( $mainSnak->getDataValue() ) ) {
						continue;
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
					}
					catch ( UsageException $e ) {
						//Ignore
					}
				}
			}
		}

		return $referenceCounter;
	}

}
