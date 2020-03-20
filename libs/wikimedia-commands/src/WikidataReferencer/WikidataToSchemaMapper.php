<?php

namespace Addwiki\Commands\Wikimedia\WikidataReferencer;

use Addwiki\Commands\Wikimedia\SparqlQueryRunner;
use Addwiki\Commands\Wikimedia\WikidataReferencer\Referencers\DateReferencer;
use Addwiki\Commands\Wikimedia\WikidataReferencer\Referencers\MultiTextReferencer;
use Addwiki\Commands\Wikimedia\WikidataReferencer\Referencers\ThingReferencer;
use Wikibase\Api\WikibaseFactory;

/**
 * Class containing detail of what types of items the script recognises and will try to reference
 * as well as how to try and reference them.
 *
 * All details withing this class are essentially mappings between Wikidata.org and Schema.org
 *
 * @author Addshore
 * @author Ainali
 */
class WikidataToSchemaMapper {

	/**
	 * This method needs to be changed to allow the script to recognize more types of item.
	 * Details of how references could be created should also be added to the getReferencerMap method.
	 *
	 * @return string[] like array( 'ItemID' => 'Schema.org_type' )
	 */
	public function getInstanceMap() {
		return array(
			'Q5' => 'Person',
			'Q571' => 'Book',
			'Q11424' => 'Movie',
		);
	}

	/**
	 * This method needs to be changed to allow the script to know how to create more references.
	 *
	 * @param WikibaseFactory $wikibaseFactory
	 * @param SparqlQueryRunner $sparqlQueryRunner
	 *
	 * @return array like array( 'Schema.org_type' => Referencer[] )
	 */
	public function getReferencerMap(
		WikibaseFactory $wikibaseFactory,
		SparqlQueryRunner $sparqlQueryRunner
	) {
		return array(
			'Book' => array(
				new ThingReferencer(
					$wikibaseFactory,
					array(
						'P50' => 'author',
						'P110' => 'illustrator',
						'P123' => 'publisher',
						'P136' => 'genre',
					)
				),
				new DateReferencer(
					$wikibaseFactory,
					array(
						'P577' => 'datePublished',
					)
				),
			),
			'Person' => array(
				new ThingReferencer(
					$wikibaseFactory,
					array(
						'P7' => 'sibling',//brother
						'P9' => 'sibling',//sister
						'P19' => 'birthPlace',
						'P20' => 'deathPlace',
						'P21' => 'gender',
						'P22' => 'parent',//father
						'P25' => 'parent',//mother
						'P26' => 'spouse',
						'P40' => 'children',
						'P27' => 'nationality',
						'P734' => 'familyName',
						'P735' => 'givenName',
					)
				),
				new DateReferencer(
					$wikibaseFactory,
					array(
						'P569' => 'birthDate',
						'P570' => 'deathDate',
					)
				)
			),
			'Movie' => array(
				new ThingReferencer(
					$wikibaseFactory,
					array(
						// Person
						'P57' => 'director',
						'P161' => 'actor',
						'P162' => 'producer',
						'P1040' => 'editor',
						'P58' => 'author',
						// Organization
						'P272' => array( 'creator', 'productionCompany' ),
						// Content
						'P364' => 'inLanguage',
						'P674' => 'character',
						'P840' => 'contentLocation',
						//Metadata
						'P166' => 'award',
						'P1657' => 'contentRating',
						//'P2047' => 'duration',//duration is a Quantity not an Item type
						'P2360' => 'audience',
					)
				),
				new MultiTextReferencer(
					$wikibaseFactory,
					array(
						'P136' => 'genre',
					),
					array(
						'P136' => function () use ( $sparqlQueryRunner ) {
							$filmGenreData = $sparqlQueryRunner->getItemIdStringsAndLabelsFromInstanceOf( 'Q201658' );
							$filmGenreRegexMap = array();
							foreach( $filmGenreData as $itemIdString => $label ) {
								if( preg_match( '/ films?/i', $label ) ) {
									$regex = '/^' .  preg_replace( '/ films?/i', '( film)?', $label ) . '$/i';
								} else {
									$regex = '/^' . $label . '( film)?' . '$/i';
								}
								$regex = preg_replace( '/science ?fiction/i', '(science ?fiction|sci-fi)', $regex );
								$filmGenreRegexMap[$itemIdString] = $regex;
							}

							return $filmGenreRegexMap;
						},
					)
				),
				new DateReferencer(
					$wikibaseFactory,
					array(
						'P577' => 'datePublished',
					)
				)
			),
		);
	}

}
