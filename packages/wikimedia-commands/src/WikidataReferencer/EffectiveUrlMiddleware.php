<?php

namespace Addwiki\Wikimedia\Commands\WikidataReferencer;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author ThinkScape / Artur Bodera
 *
 * @see https://gist.github.com/Thinkscape/43499cfafda1af8f606d
 * @see http://stackoverflow.com/a/31962446/4746236
 *
 * @todo put this in a composer package?
 */
class EffectiveUrlMiddleware {

	/**
	 * @var callable
	 */
	protected $nextHandler;
	protected string $headerName;

	/**
	 * @param string $headerName The header name to use for storing effective url
	 */
	public function __construct(
		callable $nextHandler,
		string $headerName = 'X-GUZZLE-EFFECTIVE-URL'
	) {
		$this->nextHandler = $nextHandler;
		$this->headerName = $headerName;
	}

	/**
	 * Inject effective-url header into response.
	 *
	 * @param RequestInterface $request
	 * @param array $options
	 *
	 * @return RequestInterface
	 */
	public function __invoke( RequestInterface $request, array $options ) {
		$fn = $this->nextHandler;

		return $fn( $request, $options )->then(
			fn( ResponseInterface $response ) => $response->withAddedHeader(
					$this->headerName,
					$request->getUri()->__toString()
				)
		);
	}

	/**
	 * Prepare a middleware closure to be used with HandlerStack
	 *
	 * @param string $headerName The header name to use for storing effective url
	 */
	public static function middleware( string $headerName = 'X-GUZZLE-EFFECTIVE-URL' ): callable {
		return function ( callable $handler ) use ( &$headerName ) {
			return new static( $handler, $headerName );
		};
	}
}
