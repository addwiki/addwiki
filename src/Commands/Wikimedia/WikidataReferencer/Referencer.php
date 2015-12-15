<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use Wikibase\DataModel\Entity\Item;

/**
 * @author Addshore
 */
interface Referencer {

	/**
	 * @param MicroData $microData
	 *
	 * @return bool
	 */
	public function canAddReferences( MicroData $microData );

	/**
	 * @param MicroData $microData
	 * @param Item $item
	 * @param string $sourceUrl
	 * @param string|null $sourceWiki
	 *
	 * @return int the number of references added
	 */
	public function addReferences( MicroData $microData, $item, $sourceUrl, $sourceWiki = null );

}
