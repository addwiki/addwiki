<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use linclark\MicrodataPHP\MicrodataPhp;

class MicrodataExtractor {

	/**
	 * @param string $html raw HTML
	 * @param string $type eg. "Movie"
	 *
	 * @return array[] array of microdata things
	 */
	public function extract( $html, $type ) {
		$microDatas = array();
		$md = new MicrodataPhp( array( 'html' => $html ) );

		$data = $md->obj();
		foreach ( $data->items as $microdata ) {
			//TODO also match https or protocol relative? (is this needed?)
			if ( in_array( 'http://schema.org/' . $type, $microdata->type ) ) {
				$microDatas[] = $microdata;
			}
		}
		return $microDatas;
	}

}
