<?php

namespace Addwiki\Mediawiki\Ext\Flow\Api\Service;

use Addwiki\Mediawiki\Api\Client\Action\ActionApi;
use Addwiki\Mediawiki\Api\Client\Action\Request\ActionRequest;
use Addwiki\Mediawiki\Ext\Flow\DataModel\Topic;

class TopicCreator {

	private ActionApi $api;

	public function __construct( ActionApi $api ) {
		$this->api = $api;
	}

	public function create( Topic $topic ): void {
		$this->api->request( ActionRequest::simplePost(
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
