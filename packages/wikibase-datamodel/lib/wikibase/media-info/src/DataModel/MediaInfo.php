<?php

namespace Wikibase\MediaInfo\DataModel;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\ClearableEntity;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\StatementListProvidingEntity;
use Wikibase\DataModel\Statement\StatementList;
use Wikibase\DataModel\Term\DescriptionsProvider;
use Wikibase\DataModel\Term\LabelsProvider;
use Wikibase\DataModel\Term\TermList;

/**
 * Entity describing a media file that consists of an id, labels, descriptions and statements.
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class MediaInfo
	implements StatementListProvidingEntity, LabelsProvider, DescriptionsProvider, ClearableEntity
{

	public const ENTITY_TYPE = 'mediainfo';

	/**
	 * @var MediaInfoId|null
	 */
	private $id;

	/**
	 * @var TermList
	 */
	private $labels;

	/**
	 * @var TermList
	 */
	private $descriptions;

	/**
	 * @var StatementList
	 */
	private $statements;

	/**
	 * @param MediaInfoId|null $id
	 * @param TermList|null $labels
	 * @param TermList|null $descriptions
	 * @param StatementList|null $statements
	 */
	public function __construct(
		MediaInfoId $id = null,
		TermList $labels = null,
		TermList $descriptions = null,
		StatementList $statements = null
	) {
		$this->id = $id;
		$this->labels = $labels ?: new TermList();
		$this->descriptions = $descriptions ?: new TermList();
		$this->statements = $statements ?: new StatementList();
	}

	/**
	 * @return string
	 */
	public function getType() {
		return self::ENTITY_TYPE;
	}

	/**
	 * @return MediaInfoId|null
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param EntityId $id
	 *
	 * @throws InvalidArgumentException
	 */
	public function setId( $id ) {
		if ( !( $id instanceof MediaInfoId ) ) {
			throw new InvalidArgumentException( '$id must be an instance of MediaInfoId' );
		}

		$this->id = $id;
	}

	/**
	 * @return TermList
	 */
	public function getLabels() {
		return $this->labels;
	}

	/**
	 * @return TermList
	 */
	public function getDescriptions() {
		return $this->descriptions;
	}

	/**
	 * @return StatementList
	 */
	public function getStatements() {
		return $this->statements;
	}

	/**
	 * @return bool
	 */
	public function isEmpty() {
		return $this->labels->isEmpty()
			&& $this->descriptions->isEmpty()
			&& $this->statements->isEmpty();
	}

	/**
	 * @see EntityDocument::equals
	 *
	 * @param mixed $target
	 *
	 * @return bool
	 */
	public function equals( $target ) {
		if ( $this === $target ) {
			return true;
		}

		return $target instanceof self
			&& $this->labels->equals( $target->labels )
			&& $this->descriptions->equals( $target->descriptions )
			&& $this->statements->equals( $target->statements );
	}

	/**
	 * @see EntityDocument::copy
	 *
	 * @return MediaInfo
	 */
	public function copy() {
		return clone $this;
	}

	/**
	 * @see http://php.net/manual/en/language.oop5.cloning.php
	 */
	public function __clone() {
		$this->labels = clone $this->labels;
		$this->descriptions = clone $this->descriptions;
		$this->statements = clone $this->statements;
	}

	/**
	 * @inheritDoc
	 */
	public function clear() {
		$this->labels = new TermList();
		$this->descriptions = new TermList();
		$this->statements = new StatementList();
	}

}
