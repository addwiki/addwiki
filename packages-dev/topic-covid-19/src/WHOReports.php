<?php

namespace Addwiki\Topics\Covid19;

use RuntimeException;

class WHOReports {

	private $reportHome = 'https://www.who.int/emergencies/diseases/novel-coronavirus-2019/situation-reports';
	private $reportPrefix = 'https://www.who.int/docs/default-source/coronaviruse/situation-reports/';

	private $idIndex = [];
	private $dateIndex = [];
	private $pdfIndex = [];

	public function getReportForId( $id ) {
		$this->ensureIndexIsGenerated();
		if ( !array_key_exists( $id, $this->idIndex ) ) {
			throw new RuntimeException( 'No such report' );
		}

		$pdfName = $this->idIndex[$id];
		$url = $this->reportPrefix . $this->idIndex[$id];
		$pdfDetails = $this->pdfIndex[$pdfName];
		$date = $pdfDetails['date'];
		return new WHOReport( $url, $id, $date );
	}

	public function getReportForDate( $date ) {
		$this->ensureIndexIsGenerated();
		if ( !array_key_exists( $date, $this->dateIndex ) ) {
			throw new RuntimeException( 'No such report' );
		}

		$pdfName = $this->dateIndex[$date];
		$url = $this->reportPrefix . $this->dateIndex[$date];
		$pdfDetails = $this->pdfIndex[$pdfName];
		$id = $pdfDetails['id'];
		return new WHOReport( $url, $id, $date );
	}

	/**
	 * @param string $date 20201201 for example
	 * @return string|null
	 */
	public function getUrlForDate( $date ) {
		$this->ensureIndexIsGenerated();
		if ( !array_key_exists( $date, $this->dateIndex ) ) {
			return null;
		}
		return $this->reportPrefix . $this->dateIndex[$date];
	}

	private function ensureIndexIsGenerated() {
		if ( !empty( $this->idIndex ) && !empty( $this->dateIndex ) && !empty( $this->pdfIndex ) ) {
			return;
		}

		// TODO make this match reports prior to ID 24...
		$html = file_get_contents( $this->reportHome );
		preg_match_all(
			'/\/docs\/default-source\/coronaviruse\/situation-reports\/((\d\d\d\d\d\d\d\d)-sitrep-(\d+)-covid-19\.pdf)/',
			$html,
			$matches
		);
		foreach ( $matches[1] as $key => $pdfName ) {
			$this->idIndex[$matches[3][$key]] = $pdfName;
			$this->dateIndex[$matches[2][$key]] = $pdfName;
			$this->pdfIndex[$pdfName] = [ 'id' => $matches[3][$key], 'date' => $matches[2][$key] ];
		}

		if ( empty( $this->idIndex ) || empty( $this->dateIndex ) || empty( $this->pdfIndex ) ) {
			throw new RuntimeException( 'Failed to index WHO reports' );
		}
	}

}
