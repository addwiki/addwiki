<?php

namespace Addwiki\Wikibase\Query\Tests\Unit;

use Addwiki\Wikibase\Query\WikibaseQueryService;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers WikibaseQueryService
 */
class WikibaseQueryServiceTest extends TestCase {

	private const SPARQL_ENDPOINT = 'null-sparql-endpoint';

	/**
	 * @return ClientInterface&MockObject
	 */
	private function getMockClient() {
		return $this->createMock( ClientInterface::class );
	}

	private function mockQueryArray( array $resultData ) {
		$bindings = [];
		$vars = [];
		foreach ( $resultData as $key => $entry ) {
			$vars[] = $key;
			$bindings[] = $entry;
		}
		return [
			'head' => [
				'vars' => $vars,
			],
			'results' => [
				'bindings' => $bindings,
			],
		];
	}

	private function getAMockedResultArray() {
		return $this->mockQueryArray(
			[
				[
					'item' => [
						'type' => 'uri',
						'value' => 'http://www.wikidata.org/entity/Q111',
					],
					'item2' => [
						'type' => 'uri',
						'value' => 'http://www.wikidata.org/entity/Q222',
					],
					'item2Label' => [
						'xml:lang' => 'en',
						'type' => 'literal',
						'value' => 'Hello',
					],
				],
				[
					'item' => [
						'type' => 'uri',
						'value' => 'http://www.wikidata.org/entity/Q333',
					],
					'item2' => [
						'type' => 'uri',
						'value' => 'http://www.wikidata.org/entity/Q444',
					],
				],
				[
					'item' => [
						'type' => 'uri',
						'value' => 'http://www.wikidata.org/entity/Q555',
					],
				]
			]
				);
	}

	public function provideConceptSuffixesFromQueryResult() {
		return [
			[ 'item', [ 'Q111', 'Q333', 'Q555' ] ],
			[ 'item2', [ 'Q222', 'Q444' ] ],
			[ 'foo', [] ],
			[ 'item2Label', [ 'Hello' ] ],
		];
	}

	/**
	 * @dataProvider provideConceptSuffixesFromQueryResult
	 */
	public function testGetConceptSuffixesFromQueryResult( string $namedValue, array $expected ): void {
		$arrayIn = $this->getAMockedResultArray();
		$sut = new WikibaseQueryService( $this->getMockClient(), self::SPARQL_ENDPOINT );
		$this->assertEquals(
			$expected,
			$sut->getConceptSuffixesFromQueryResult( $arrayIn, $namedValue )
		);
	}

	public function provideConceptSuffixesFromQueryResultMulti() {
		return [
			// Just one
			[ [ 'item' ], [ [ 'item' => 'Q111' ], [ 'item' => 'Q333' ], [ 'item' => 'Q555' ] ] ],
			// Mutliple
			[ [ 'item', 'item2' ], [ [ 'item' => 'Q111', 'item2' => 'Q222' ], [ 'item' => 'Q333', 'item2' => 'Q444' ], [ 'item' => 'Q555' ] ] ],
		];
	}

	/**
	 * @dataProvider provideConceptSuffixesFromQueryResultMulti
	 */
	public function testGetConceptSuffixesFromQueryResultMulti( array $namedValues, array $expected ): void {
		$arrayIn = $this->getAMockedResultArray();
		$sut = new WikibaseQueryService( $this->getMockClient(), self::SPARQL_ENDPOINT );
		$this->assertEquals(
			$expected,
			$sut->getConceptSuffixesFromQueryResultMulti( $arrayIn, $namedValues )
		);
	}

}
