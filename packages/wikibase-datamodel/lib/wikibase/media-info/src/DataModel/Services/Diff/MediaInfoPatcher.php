<?php

namespace Wikibase\MediaInfo\DataModel\Services\Diff;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Services\Diff\EntityDiff;
use Wikibase\DataModel\Services\Diff\EntityPatcherStrategy;
use Wikibase\DataModel\Services\Diff\StatementListPatcher;
use Wikibase\DataModel\Services\Diff\TermListPatcher;
use Wikibase\MediaInfo\DataModel\MediaInfo;

/**
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Kreuz
 */
class MediaInfoPatcher implements EntityPatcherStrategy {

	/**
	 * @var TermListPatcher
	 */
	private $termListPatcher;

	/**
	 * @var StatementListPatcher
	 */
	private $statementListPatcher;

	public function __construct() {
		$this->termListPatcher = new TermListPatcher();
		$this->statementListPatcher = new StatementListPatcher();
	}

	/**
	 * @param string $entityType
	 *
	 * @return bool
	 */
	public function canPatchEntityType( $entityType ) {
		return $entityType === 'mediainfo';
	}

	/**
	 * @param EntityDocument $entity
	 * @param EntityDiff $patch
	 *
	 * @throws InvalidArgumentException
	 */
	public function patchEntity( EntityDocument $entity, EntityDiff $patch ) {
		if ( !( $entity instanceof MediaInfo ) ) {
			throw new InvalidArgumentException( '$entity must be an instance of MediaInfo' );
		}

		$this->termListPatcher->patchTermList(
			$entity->getLabels(),
			$patch->getLabelsDiff()
		);
		$this->termListPatcher->patchTermList(
			$entity->getDescriptions(),
			$patch->getDescriptionsDiff()
		);

		$this->statementListPatcher->patchStatementList(
			$entity->getStatements(),
			$patch->getClaimsDiff()
		);
	}

}
