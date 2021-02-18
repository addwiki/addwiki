<?php

namespace Addwiki\Wikimedia\Commands\WikidataReferencer\Referencers;

use Addwiki\Wikimedia\Commands\WikidataReferencer\MicroData\MicroData;
use Wikibase\DataModel\Entity\Item;

interface Referencer {

	/**
	 *
	 * @return int the number of references added
	 */
	public function addReferences( MicroData $microData, Item $item, string $sourceUrl ): int;

}
