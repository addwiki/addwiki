<?php

namespace Addwiki\Wikimedia\Commands\WikidataReferencer\MicroData;

use linclark\MicrodataPHP\MicrodataPhp;

/**
 * @author Addshore
 */
class MicroDataExtractor {

	/**
	 * @param string $html raw HTML
	 *
	 * @return MicroData[] array of microdata things
	 */
	public function extract( string $html ): array {
		$microDatas = [];
		$md = new MicrodataPhp( [ 'html' => $html ] );

		$data = $md->obj();
		foreach ( $data->items as $microData ) {
			$microDatas[] = new MicroData( $microData );
		}
		return $microDatas;
	}

}
