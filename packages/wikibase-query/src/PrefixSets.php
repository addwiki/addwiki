<?php

namespace Addwiki\Wikibase\Query;

/**
 * Mainly taken from https://www.mediawiki.org/wiki/Wikibase/Indexing/RDF_Dump_Format#Full_list_of_prefixes
 * As well as a method to generate more.
 */
class PrefixSets {

	public const GENERAL = [
		'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
		'xsd' => 'http://www.w3.org/2001/XMLSchema#',
		'ontolex' => 'http://www.w3.org/ns/lemon/ontolex#',
		'dct' => 'http://purl.org/dc/terms/',
		'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
		'owl' => 'http://www.w3.org/2002/07/owl#',
		'skos' => 'http://www.w3.org/2004/02/skos/core#',
		'schema' => 'http://schema.org/',
		'cc' => 'http://creativecommons.org/ns#',
		'geo' => 'http://www.opengis.net/ont/geosparql#',
		'prov' => 'http://www.w3.org/ns/prov#',
		'wikibase' => 'http://wikiba.se/ontology#',
		'bd' => 'http://www.bigdata.com/rdf#',
		'hint' => 'http://www.bigdata.com/queryHints#',
	];

	public const WIKIDATA = [
		'wdata' => 'http://www.wikidata.org/wiki/Special:EntityData/',
		'wd' => 'http://www.wikidata.org/entity/',
		'wdt' => 'http://www.wikidata.org/prop/direct/',
		'wdtn' => 'http://www.wikidata.org/prop/direct-normalized/',
		'wds' => 'http://www.wikidata.org/entity/statement/',
		'p' => 'http://www.wikidata.org/prop/',
		'wdref' => 'http://www.wikidata.org/reference/',
		'wdv' => 'http://www.wikidata.org/value/',
		'ps' => 'http://www.wikidata.org/prop/statement/',
		'psv' => 'http://www.wikidata.org/prop/statement/value/',
		'psn' => 'http://www.wikidata.org/prop/statement/value-normalized/',
		'pq' => 'http://www.wikidata.org/prop/qualifier/',
		'pqv' => 'http://www.wikidata.org/prop/qualifier/value/',
		'pqn' => 'http://www.wikidata.org/prop/qualifier/value-normalized/',
		'pr' => 'http://www.wikidata.org/prop/reference/',
		'prv' => 'http://www.wikidata.org/prop/reference/value/',
		'prn' => 'http://www.wikidata.org/prop/reference/value-normalized/',
		'wdno' => 'http://www.wikidata.org/prop/novalue/',
	];

	// From https://commons.wikimedia.org/wiki/Special:EntityData/M2154214.ttl
	public const WIKIMEDIA_COMMONS = [
		'sdcdata' => 'https://commons.wikimedia.org/wiki/Special:EntityData/',
		'sdc' => 'https://commons.wikimedia.org/entity/',
		'sdct' => 'https://commons.wikimedia.org/prop/direct/',
		'sdctn' => 'https://commons.wikimedia.org/prop/direct-normalized/',
		'sdcs' => 'https://commons.wikimedia.org/entity/statement/',
		'sdcp' => 'https://commons.wikimedia.org/prop/',
		'sdcref' => 'https://commons.wikimedia.org/reference/',
		'sdcv' => 'https://commons.wikimedia.org/value/',
		'sdcps' => 'https://commons.wikimedia.org/prop/statement/',
		'sdcpsv' => 'https://commons.wikimedia.org/prop/statement/value/',
		'sdcpsn' => 'https://commons.wikimedia.org/prop/statement/value-normalized/',
		'sdcpq' => 'https://commons.wikimedia.org/prop/qualifier/',
		'sdcpqv' => 'https://commons.wikimedia.org/prop/qualifier/value/',
		'sdcpqn' => 'https://commons.wikimedia.org/prop/qualifier/value-normalized/',
		'sdcpr' => 'https://commons.wikimedia.org/prop/reference/',
		'sdcprv' => 'https://commons.wikimedia.org/prop/reference/value/',
		'sdcprn' => 'https://commons.wikimedia.org/prop/reference/value-normalized/',
		'sdcno' => 'https://commons.wikimedia.org/prop/novalue/',
	];

	public static function generate( string $prefixPrefix, string $procol, string $host ): array {
		$new = [];
		foreach ( self::WIKIMEDIA_COMMONS as $key => $value ) {
			$new[str_replace( 'sdc', $prefixPrefix, $key )] = str_replace( 'https://commons.wikimedia.org', $procol . '://' . $host, $value );
		}
		return $new;
	}

}
