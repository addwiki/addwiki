<?php

namespace Addwiki\Mediawiki\Ext\Flow\Api\Service;

use Addwiki\Mediawiki\Api\Client\MediawikiApi;
use Addwiki\Mediawiki\Api\Client\SimpleRequest;
use Addwiki\Mediawiki\Ext\Flow\DataModel\Topic;

class TopicCreator {

	private MediawikiApi $api;

	/**
	 * @param MediawikiApi $api
	 */
	public function __construct( MediawikiApi $api ) {
		$this->api = $api;
	}

	public function create( Topic $topic ): void {
		$this->api->postRequest( new SimpleRequest(
			'flow',
			[
				'submodule' => 'new-topic',
				'page' => $topic->getPageName(),
				'nttopic' => $topic->getHeader(),
				'ntcontent' => $topic->getContent(),
				'token' => $this->api->getToken()
			]
		) );
	}

}
