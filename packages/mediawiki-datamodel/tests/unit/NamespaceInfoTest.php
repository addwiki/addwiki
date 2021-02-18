<?php

namespace Addwiki\Mediawiki\DataModel\Tests\Unit;

use Addwiki\Mediawiki\DataModel\NamespaceInfo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Addwiki\Mediawiki\DataModel\NamespaceInfo
 * @author gbirke
 */
class NamespaceInfoTest extends TestCase {

	/**
	 * @dataProvider provideValidConstruction
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

}
