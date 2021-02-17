<?php

namespace Addwiki\Wikibase\Api\Lookup;

use Addwiki\Wikibase\DataModel\Services\Lookup\EntityLookup;
use Addwiki\Wikibase\DataModel\Services\Lookup\ItemLookup;
use Wikibase\DataModel\Entity\ItemId;

/**
 * @access private
 *
 * @author Thomas Pellissier Tanon
 * @author Addshore
 */
class ItemApiLookup implements ItemLookup {

	/**
	 * @var EntityLookup
	 */
	private $entityLookup;

	/**
	 * @param EntityLookup $entityLookup
	 */
	public function __construct( EntityLookup $entityLookup ) {
		$this->entityLookup = $entityLookup;
	}

	/**
	 * @see ItemLookup::getItemForId
	 */
	public function getItemForId( ItemId $itemId ) {
		return $this->entityLookup->getEntity( $itemId );
	}
}
