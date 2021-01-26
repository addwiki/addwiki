<?php

namespace Mediawiki\Db\Service;

use PDO;

class PageFileExtensionListGetter {

	/**
	 * @var PDO
	 */
	protected $db;

	/**
	 * @var string
	 */
	protected $fileExtension;

	/**
	 * @param PDO $db
	 * @param string $fileExtension
	 */
	public function __construct( PDO $db, $fileExtension ) {
		$this->db = $db;
		$this->fileExtension = $fileExtension;
	}

	/**
	 * @return array of pageids
	 */
	public function getPageIds() {
		$statement = $this->db->prepare( "SELECT page_id from page where page_title like :regex and page_namespace = 6" );
		$statement->execute( [ ':regex' => '%\.' . $this->fileExtension ] );
		$rows = $statement->fetchAll();

		$pageids = [];
		foreach ( $rows as $row ) {
			$pageids[] = intval( $row['page_id'] );
		}

		return $pageids;
	}

}
