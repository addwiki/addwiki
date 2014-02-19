<?php

namespace Mediawiki\Bot\Tasks;

/**
 * Simple interface for tasks that can be run
 */
interface Task {

	/**
	 * @return bool success
	 */
	public function run();

} 