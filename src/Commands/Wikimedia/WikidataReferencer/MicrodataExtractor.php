<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use linclark\MicrodataPHP\MicrodataPhp;

/**
 * @author Addshore
 */
class MicrodataExtractor {

	/**
	 * @param string $html raw HTML
	 *
	 * @return MicroData[] array of microdata things
	 */
	public function extract( $html ) {
		$microDatas = array();
		$md = new MicrodataPhp( array( 'html' => $html ) );

		$data = $md->obj();
		foreach ( $data->items as $microData ) {
			$microDatas[] = new MicroData( $microData );
		}
		return $microDatas;
	}

}
