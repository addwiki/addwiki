<?php

namespace Addwiki\Wikibase\Api\Lookup;

use Addwiki\Wikibase\DataModel\Services\Lookup\EntityLookup;
use Addwiki\Wikibase\DataModel\Services\Lookup\PropertyLookup;
use Wikibase\DataModel\Entity\PropertyId;

/**
 * @access private
 *
 * @author Thomas Pellissier Tanon
 * @author Addshore
 */
class PropertyApiLookup implements PropertyLookup {

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
	 * @see ItemLookup::getPropertyForId
	 */
	public function getPropertyForId( PropertyId $propertyId ) {
		return $this->entityLookup->getEntity( $propertyId );
	}
}
