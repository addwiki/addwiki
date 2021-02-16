<?php

namespace Mediawiki\DataModel\Test;

use InvalidArgumentException;
use Mediawiki\DataModel\NamespaceInfo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mediawiki\DataModel\NamespaceInfo
 * @author gbirke
 */
class NamespaceInfoTest extends TestCase {
	/**
	 * @dataProvider provideValidConstruction
	 * @param int $id
	 * @param string $canonicalName
	 * @param string $localName
	 * @param string $caseHandling
	 * @param null $defaultContentModel
	 * @param array $aliases
	 */
	public function testValidConstruction( $id, $canonicalName, $localName, $caseHandling, $defaultContentModel = null,
		$aliases = []
	) {
		$namespace = new NamespaceInfo( $id, $canonicalName, $localName, $caseHandling, $defaultContentModel, $aliases );
		$this->assertSame( $id, $namespace->getId() );
		$this->assertSame( $canonicalName, $namespace->getCanonicalName() );
		$this->assertSame( $localName, $namespace->getLocalName() );
		$this->assertSame( $caseHandling, $namespace->getCaseHandling() );
		$this->assertSame( $defaultContentModel, $namespace->getDefaultContentModel() );
		$this->assertSame( $aliases, $namespace->getAliases() );
	}

	public function provideValidConstruction() {
		return [
		[ -2, 'Media', 'Media', 'first-letter' ],
		[ 0, '', '', 'first-letter' ],
		[ 4, 'Project', 'Wikipedia', 'first-letter' ],
		[ 2302, 'Gadget definition', 'Gadget definition', 'case-sensitive', 'GadgetDefinition' ],
		[ 2302, 'Gadget definition', 'Gadget definition', 'case-sensitive', 'GadgetDefinition', [ 'GD' ] ],
		];
	}

	/**
	 * @param mixed $id
	 * @param mixed $canonicalName
	 * @param mixed $localName
	 * @param mixed $caseHandling
	 * @param null $defaultContentModel
	 * @param array $aliases
	 *
	 * @dataProvider provideInvalidConstruction
	 */
	public function testInvalidConstruction( $id, $canonicalName, $localName, $caseHandling, $defaultContentModel = null,
		$aliases = []
	) {
		$this->expectException( InvalidArgumentException::class );
		new NamespaceInfo( $id, $canonicalName, $localName, $caseHandling, $defaultContentModel, $aliases );
	}

	public function provideInvalidConstruction() {
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
