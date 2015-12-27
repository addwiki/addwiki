<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

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
