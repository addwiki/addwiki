<?php

namespace Addwiki\Wikimedia\Commands\WikidataReferencer\Referencers;

use Addwiki\Mediawiki\Api\Client\Action\Exception\UsageException;
use Addwiki\Mediawiki\DataModel\EditInfo;
use Addwiki\Wikibase\Api\WikibaseFactory;
use Addwiki\Wikimedia\Commands\WikidataReferencer\DataModelUtils;
use Addwiki\Wikimedia\Commands\WikidataReferencer\MicroData\MicroData;
use DataValues\TimeValue;
use DateTime;
use Exception;
use ValueParsers\EraParser;
use ValueParsers\IsoTimestampParser;
use ValueParsers\MonthNameUnlocalizer;
use ValueParsers\PhpDateTimeParser;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;

class DateReferencer implements Referencer {

	private WikibaseFactory $wikibaseFactory;

	/**
	 * @var string[]
	 */
	private array $propMap = [];

	private PhpDateTimeParser $timeParser;

	/**
	 * @param WikibaseFactory $wikibaseFactory
	 * @param string[] $propMap of propertyId strings to schema.org properties
	 *          eg. 'P577' => 'datePublished'
	 */
	public function __construct( WikibaseFactory $wikibaseFactory, array $propMap ) {
		$this->wikibaseFactory = $wikibaseFactory;
		$this->propMap = $propMap;
		$this->timeParser = new PhpDateTimeParser(
			new MonthNameUnlocalizer( [] ),
			new EraParser(),
			new IsoTimestampParser()
		);
	}

	/**
	 * @return int
	 */
	public function addReferences( MicroData $microData, Item $item, string $sourceUrl ): int {
		$referenceCounter = 0;

		foreach ( $this->propMap as $propertyIdString => $schemaPropertyString ) {
			/** @var TimeValue[] $timeValues */
			$timeValues = [];
			foreach ( $microData->getProperty( $schemaPropertyString, MicroData::PROP_STRING ) as $propertyValue ) {
				try{
					$date = new DateTime( trim( $propertyValue ) );
					$timeValues[] = $this->timeParser->parse( $date->format( 'Y m d' ) );
				} catch ( Exception $exception ) {

				}
			}

			$statements = $item->getStatements()->getByPropertyId( new PropertyId( $propertyIdString ) );

			foreach ( $timeValues as $timeValue ) {
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

					if ( !$timeValue->equals( $mainSnak->getDataValue() ) ) {
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
