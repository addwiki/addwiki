<?php

namespace Addwiki\Mediawiki\Api\Client;

use Addwiki\Mediawiki\DataModel\Pages;
use Exception;

/**
 * Class CategoryLoopException
 * @package Addwiki\Mediawiki\Api\Client
 */
class CategoryLoopException extends Exception {

	/** @var Pages */
	protected $categoryPath;

	/**
	 * @param Pages $path
	 */
	public function setCategoryPath( Pages $path ) {
		$this->categoryPath = $path;
	}

	/**
	 * Get the path of Pages that comprise the category loop. The first item in this list is also a
	 * child page of the last item.
	 * @return Pages The set of category Pages that comprise the category loop.
	 */
	public function getCategoryPath() {
		return $this->categoryPath;
	}

}
