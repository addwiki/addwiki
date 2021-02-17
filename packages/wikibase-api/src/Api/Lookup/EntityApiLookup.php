<?php

namespace Addwiki\Wikibase\Api\Lookup;

use Addwiki\Wikibase\Api\Service\RevisionGetter;
use Addwiki\Wikibase\DataModel\Services\Lookup\EntityLookup;
use Wikibase\DataModel\Entity\EntityId;

/**
 * @author Addshore
 *
 * @access private
 */
class EntityApiLookup implements EntityLookup {

	/**
	 * @var RevisionGetter
	 */
	private $revisionGetter;

	/**
	 * @param RevisionGetter $revisionGetter
	 */
	public function __construct( RevisionGetter $revisionGetter ) {
		$this->revisionGetter = $revisionGetter;
	}

	/**
	 * @see EntityLookup::getEntity
	 */
	public function getEntity( EntityId $entityId ) {
		$revision = $this->revisionGetter->getFromId( $entityId );

		if ( !$revision ) {
			return null;
		}

		return $revision->getContent()->getData();
	}

	/**
	 * @see EntityLookup::hasEntity
	 */
	public function hasEntity( EntityId $entityId ) {
		$revision = $this->revisionGetter->getFromId( $entityId );
		return (bool)$revision;
	}
}
