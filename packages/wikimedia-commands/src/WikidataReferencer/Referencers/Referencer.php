<?php

namespace Addwiki\Commands\Wikimedia\WikidataReferencer\Referencers;

use Addwiki\Commands\Wikimedia\WikidataReferencer\MicroData\MicroData;
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
