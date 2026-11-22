<?php
/**
 * Aufbereitung der Mengenmeldung.
 *
 * @package CatCodePackagingReport
 */

namespace CatCode\PackagingReport\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Der Bericht, den man in LUCID und beim dualen System einträgt.
 */
final class Report {

	/**
	 * Zeilen für einen Zeitraum: jede Materialart, auch mit 0 —
	 * die Eingabemaske fragt alle ab, und „0“ ist eine Aussage.
	 *
	 * @param string $from Datum von.
	 * @param string $to   Datum bis.
	 * @return array<int,array{code:string,label:string,grams:float,kg:string,kg_raw:string}>
	 */
	public static function rows( string $from, string $to ): array {
		$totals = Repository::totals( $from, $to );
		$rows   = array();

		foreach ( Materials::all() as $code => $label ) {
			$grams = (float) ( $totals[ $code ] ?? 0 );

			$rows[] = array(
				'code'   => $code,
				'label'  => $label,
				'grams'  => $grams,
				'kg'     => Materials::to_kg( $grams ),
				'kg_raw' => Materials::to_kg_raw( $grams ),
			);
		}

		return $rows;
	}

}
