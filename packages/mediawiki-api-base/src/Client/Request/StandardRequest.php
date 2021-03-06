<?php

namespace Addwiki\Mediawiki\Api\Client\Request;

abstract class StandardRequest implements Request, HasSimpleFactory {

	use SimpleFactoryTrait;

	use MethodTrait;
	use HeadersTrait;
	use ParametersTrait;
	use MultipartTrait;

}
