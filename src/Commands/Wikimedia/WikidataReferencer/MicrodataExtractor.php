<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use linclark\MicrodataPHP\MicrodataPhp;

class MicrodataExtractor {

	/**
	 * @param string $html raw HTML
	 * @param string $type eg. "Movie"
	 *
	 * @return MicroData[] array of microdata things
	 */
	public function extract( $html, $type ) {
		$microDatas = array();
		$md = new MicrodataPhp( array( 'html' => $html ) );

		$data = $md->obj();
		foreach ( $data->items as $microData ) {
			$microData = new MicroData( $microData );
			//TODO also match https or protocol relative? (is this needed?)
			if ( $microData->hasType( $type ) ) {
				$microDatas[] = $microData;
			}
		}
		return $microDatas;
	}

}
