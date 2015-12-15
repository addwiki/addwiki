<?php

namespace Mediawiki\Bot\Commands\Wikimedia\WikidataReferencer;

use linclark\MicrodataPHP\MicrodataPhp;

/**
 * @author Addshore
 */
class MicrodataExtractor {

	/**
	 * @var string Type of microdata to extract eg. "Movie"
	 */
	private $type;

	public function __construct( $type ) {
		$this->type = $type;
	}

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
			$microData = new MicroData( $microData );
			if ( $microData->hasType( $this->type ) ) {
				$microDatas[] = $microData;
			}
		}
		return $microDatas;
	}

}
