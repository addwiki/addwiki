<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\NamespaceInfo;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\NamespaceInfo
 * @author gbirke
 */
class NamespaceInfoTest extends TestCase {
	/**
	 * @dataProvider provideValidConstruction
	 * @param null $defaultContentModel
	 */
	public function testValidConstruction( int $id, string $canonicalName, string $localName, string $caseHandling, $defaultContentModel = null,
		array $aliases = []
	): void {
		$namespace = new NamespaceInfo( $id, $canonicalName, $localName, $caseHandling, $defaultContentModel, $aliases );
		$this->assertSame( $id, $namespace->getId() );
		$this->assertSame( $canonicalName, $namespace->getCanonicalName() );
		$this->assertSame( $localName, $namespace->getLocalName() );
		$this->assertSame( $caseHandling, $namespace->getCaseHandling() );
		$this->assertSame( $defaultContentModel, $namespace->getDefaultContentModel() );
		$this->assertSame( $aliases, $namespace->getAliases() );
	}

	public function provideValidConstruction(): array {
		return [
		[ -2, 'Media', 'Media', 'first-letter' ],
		[ 0, '', '', 'first-letter' ],
		[ 4, 'Project', 'Wikipedia', 'first-letter' ],
		[ 2302, 'Gadget definition', 'Gadget definition', 'case-sensitive', 'GadgetDefinition' ],
		[ 2302, 'Gadget definition', 'Gadget definition', 'case-sensitive', 'GadgetDefinition', [ 'GD' ] ],
		];
	}

	/**
	 * @param float[]|string[] $id
	 * @param string[] $canonicalName
	 * @param int[]|string[]|null[] $localName
	 * @param int[]|string[]|null[] $caseHandling
	 * @param null $defaultContentModel
	 *
	 * @dataProvider provideInvalidConstruction
	 * @param int[]|string[]|null[] $aliases
	 */
	public function testInvalidConstruction( array $id, array $canonicalName, array $localName, array $caseHandling, $defaultContentModel = null,
		array $aliases = []
	): void {
		$this->expectException( InvalidArgumentException::class );
		new NamespaceInfo( $id, $canonicalName, $localName, $caseHandling, $defaultContentModel, $aliases );
	}

	public function provideInvalidConstruction(): array {
		return [
		[ 0.5, 'Media', 'Media', 'first-letter' ],
		[ '0', '', '', 'first-letter' ],
		[ -2, null, 'Media', 'first-letter' ],
		[ -2, 'Media', null, 'first-letter' ],
		[ 4, 'Project', 'Wikipedia', 'first-letter', 5 ],
		[ 2302, null, 'Gadget definition', 'case-sensitive', 'GadgetDefinition' ],
		[ 4, 'Project', 'Wikipedia', 'first-letter', 5 ],
		[ 4, 'Project', 'Wikipedia', 'first-letter', 'GadgetDefinition', 'notanalias' ],
		];
	}

}
