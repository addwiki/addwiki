<?php

namespace Mediawiki\Db\Service;

use FluentPDO;
use Mediawiki\DataModel\User;
use MediaWiki\Services\UserGetter;
use PDO;

/**
 * @author Bene* < benestar.wikimedia@gmail.com >
 */
class DatabaseUserGetter implements UserGetter {

	/**
	 * @var FluentPDO
	 */
	private $db;

	/**
	 * @param PDO $db
	 */
	public function __construct( PDO $db ) {
		$this->db = new FluentPDO( $db );
	}

	/**
	 * @param string $userName
	 *
	 * @return User
	 */
	public function getUser( $userName ) {
		$users = $this->getUsers( array( $userName ) );
		return count( $users ) ? $user[0] : null;
	}

	/**
	 * @param string[] $userNames
	 *
	 * @return User[]
	 */
	public function getUsers( array $userNames ) {
		$query = $this->db->from( 'user' )
			->select( 'user_id', 'user_name', 'user_editcount', 'user_registration', 'ug_group' )
			->leftJoin( 'user_groups ON user_id = ug_user' )
			->where( 'user_name', $userNames );

		$users = array();
		$rows = $query->fetchAll();
		
		$userGroups = $this->getGroupsPerUser( $rows );

		foreach ( $rows as $row ) {
			$users[] = $this->getUserFromRow( $row, $userGroups[$row['user_id']] );
		}

		return $users;
	}

	private function getGroupsPerUser( array $rows ) {
		$userGroups = array();

		foreach ( $rows as $row ) {
			$userGroups[$row['user_id']][] = $row['ug_group'];
		}

		return $userGroups;
	}

	private function getUserFromRow( array $row, array $groups ) {
		return new User(
			$row['user_name'],
			$row['user_id'],
			$row['user_editcount'],
			$row['user_registration'],
			array( 'groups' => $groups ),
			array(),
			''
		);
	}

}
