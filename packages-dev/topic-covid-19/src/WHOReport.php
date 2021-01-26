<?php

namespace Addwiki\Topics\Covid19;

class WHOReport {

	/**
	 * @var string
	 */
	private $url;

	private $text;

	private $id;
	private $date;

	private const VALUE_TYPE_CASE = 'cases';
	private const VALUE_TYPE_DEATH = 'deaths';

	/**
	 * WHOReport constructor.
	 * @param string $url Full URL to a report
	 */
	public function __construct( $url, $id, $date ) {
		$this->url = $url;
		$this->id = $id;
		$this->date = $date;
	}

	public function getId() {
		return $this->id;
	}

	public function getDate() {
		return $this->date;
	}

	/**
	 * @param string $reporter
	 * @param string $value One of the VALUE_ constants
	 * @return int Value from the WHO report
	 */
	public function getValue( $reporter, $value ) {
		$text = $this->getPdfText();
		$this->assertTableHeadersAreAsExpected( $text );
		return $this->getValueUsingRegex( $text, $reporter, $value );
	}

	private function getTableHeadingRegex() {
		// Characters that appear in headings that get picked up in off ways by the pdf parse
		$betweenExpression = '[\s1†‡§]';

		// A collection of chars in headings with no whitespaces (used to check the heading order
		// is the same). Each char will have the regex in $gapCharExpression inserted between
		$headings = [
			'ReportingCountry/Territory/Area',
			'Totalconfirmedcases',
			'Totalconfirmednewcases',
			'Totaldeaths',
			'Totalnewdeaths',
			'Transmissionclassification',
			'Dayssincelastreportedcase',
		];

		$regexParts = [];
		foreach ( $headings as $headingChars ) {
			$regexParts[] = implode( $betweenExpression . '*', str_split( $headingChars ) );
		}
		$regex = implode( $betweenExpression . '*', $regexParts );
		$regex = '#' . $regex . '#';
		return $regex;
	}

	private function getValueUsingRegex( $text, $reporter, $valueType ) {
		// Remove all new lines so that things that are split over multiple lines are easier to
		// match
		$text = trim( preg_replace( '/\s\s+/', ' ', $text ) );

		$pattern = '#(' . preg_quote( $reporter ) . '(?:\s+?\([\sa-zA-Z]+\))?)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+#m';
		preg_match_all( $pattern, $text, $matches );

		// Sanity check that we only find 1 line in the text version of the PDF that looks like
		// the table row
		if ( count( $matches[0] ) > 1 ) {
			throw new \RuntimeException( 'More than 1 pdf table row extracted, bailing...' );
		}

		switch ( $valueType ) {
			case self::VALUE_TYPE_CASE:
				$value = (int)$matches[2][0];
				break;
			case self::VALUE_TYPE_DEATH:
				$value = (int)$matches[4][0];
				break;
			default:
				throw new \RuntimeException( 'Unknown value type: ' . $valueType );
		}

		if ( $value === 0 ) {
			echo $pattern . PHP_EOL;
			die( 'Failed to correctly match value? got: ' . $value . PHP_EOL );
		}

		return $value;
	}

	private function assertTableHeadersAreAsExpected( $text ) {
		$regex = $this->getTableHeadingRegex();
		if ( !preg_match( $this->getTableHeadingRegex(), $text ) ) {
			// Maybe the format of the table has changed?
			// In which case we will need to update code..
			var_dump( $text );
			var_dump( $regex );
			throw new \RuntimeException( 'Expected table header not found in WHO report' );
		}
	}

	private function getPdfText() {
		if ( $this->text ) {
			return $this->text;
		}

		$parser = new \Smalot\PdfParser\Parser();
		$pdf    = $parser->parseFile( $this->url );

		$this->text = $pdf->getText();
		return $this->text;
	}

}
