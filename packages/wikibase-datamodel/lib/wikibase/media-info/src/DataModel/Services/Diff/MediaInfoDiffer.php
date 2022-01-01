<?php

namespace Wikibase\MediaInfo\DataModel\Services\Diff;

use Diff\Differ\MapDiffer;
use InvalidArgumentException;
use Wikibase\DataModel\Entity\EntityDocument;
use Wikibase\DataModel\Services\Diff\EntityDiff;
use Wikibase\DataModel\Services\Diff\EntityDifferStrategy;
use Wikibase\DataModel\Services\Diff\StatementListDiffer;
use Wikibase\MediaInfo\DataModel\MediaInfo;

/**
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Kreuz
 */
class MediaInfoDiffer implements EntityDifferStrategy {

	/**
	 * @var MapDiffer
	 */
	private $recursiveMapDiffer;

	/**
	 * @var StatementListDiffer
	 */
	private $statementListDiffer;

	public function __construct() {
		$this->recursiveMapDiffer = new MapDiffer( true );
		$this->statementListDiffer = new StatementListDiffer();
	}

	/**
	 * @param string $entityType
	 *
	 * @return bool
	 */
	public function canDiffEntityType( $entityType ) {
		return $entityType === 'mediainfo';
	}

	/**
	 * @param EntityDocument $from
	 * @param EntityDocument $to
	 *
	 * @return EntityDiff
	 * @throws InvalidArgumentException
	 */
	public function diffEntities( EntityDocument $from, EntityDocument $to ) {
		$this->assertIsMediaInfo( $from );
		$this->assertIsMediaInfo( $to );

		return $this->diffMediaInfos( $from, $to );
	}

	/**
	 * @param EntityDocument $mediaInfo
	 * @phan-assert MediaInfo $mediaInfo
	 */
	private function assertIsMediaInfo( EntityDocument $mediaInfo ) {
		if ( !( $mediaInfo instanceof MediaInfo ) ) {
			throw new InvalidArgumentException( '$mediaInfo must be an instance of MediaInfo' );
		}
	}

	public function diffMediaInfos( MediaInfo $from, MediaInfo $to ) {
		$diffOps = $this->recursiveMapDiffer->doDiff(
			$this->toDiffArray( $from ),
			$this->toDiffArray( $to )
		);

		$diffOps['claim'] = $this->statementListDiffer->getDiff(
			$from->getStatements(),
			$to->getStatements()
		);

		return new EntityDiff( $diffOps );
	}

	private function toDiffArray( MediaInfo $mediaInfo ) {
		$array = [];

		$array['label'] = $mediaInfo->getLabels()->toTextArray();
		$array['description'] = $mediaInfo->getDescriptions()->toTextArray();

		return $array;
	}

	/**
	 * @param EntityDocument $entity
	 *
	 * @return EntityDiff
	 * @throws InvalidArgumentException
	 */
	public function getConstructionDiff( EntityDocument $entity ) {
		$this->assertIsMediaInfo( $entity );
		return $this->diffEntities( new MediaInfo(), $entity );
	}

	/**
	 * @param EntityDocument $entity
	 *
	 * @return EntityDiff
	 * @throws InvalidArgumentException
	 */
	public function getDestructionDiff( EntityDocument $entity ) {
		$this->assertIsMediaInfo( $entity );
		return $this->diffEntities( $entity, new MediaInfo() );
	}

}
