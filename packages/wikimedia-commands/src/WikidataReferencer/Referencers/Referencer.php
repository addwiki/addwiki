<?php

namespace Addwiki\Wikimedia\Commands\WikidataReferencer\Referencers;

use Addwiki\Wikimedia\Commands\WikidataReferencer\MicroData\MicroData;
use Wikibase\DataModel\Entity\Item;

/**
 * @author Addshore
 */
interface Referencer {

	/**
	 * @param MicroData $microData
	 * @param Item $item
	 * @param string $sourceUrl
	 *
	 * @return int the number of references added
	 */
	public function addReferences( MicroData $microData, $item, $sourceUrl );

}
