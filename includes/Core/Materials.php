<?php
/**
 * LUCID-Materialarten.
 *
 * @package CatCodePackagingReport
 */

namespace CatCode\PackagingReport\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Die Materialarten, in denen das Verpackungsregister die Mengen erwartet.
 *
 * Die Reihenfolge ist die der LUCID-Eingabemaske — wer den Bericht abtippt,
 * geht die Zeilen von oben nach unten durch und soll nicht suchen müssen.
 */
final class Materials {

	/**
	 * Codes und Bezeichnungen.
	 *
	 * @return array<string,string>
	 */
	public static function all(): array {
		return array(
			'glas'            => __( 'Glass', 'catcode-packaging-report' ),
			'ppk'             => __( 'Paper, board, cardboard', 'catcode-packaging-report' ),
			'fe'              => __( 'Ferrous metals', 'catcode-packaging-report' ),
			'alu'             => __( 'Aluminium', 'catcode-packaging-report' ),
			'kunststoff'      => __( 'Plastics', 'catcode-packaging-report' ),
			'getraenkekarton' => __( 'Beverage carton packaging', 'catcode-packaging-report' ),
			'verbund'         => __( 'Other composite packaging', 'catcode-packaging-report' ),
			'sonstige'        => __( 'Other materials', 'catcode-packaging-report' ),
		);
	}

	/**
	 * Gültiger Materialcode?
	 *
	 * @param string $code Code.
	 */
	public static function exists( string $code ): bool {
		return isset( self::all()[ $code ] );
	}

	/**
	 * Bezeichnung zu einem Code.
	 *
	 * @param string $code Code.
	 */
	public static function label( string $code ): string {
		return self::all()[ $code ] ?? $code;
	}

	/**
	 * Gramm in Kilogramm, wie LUCID sie erwartet: drei Nachkommastellen.
	 *
	 * @param float $grams Gramm.
	 */
	public static function to_kg( float $grams ): string {
		return number_format_i18n( $grams / 1000, 3 );
	}

	/**
	 * Dieselbe Umrechnung, aber maschinenlesbar für den CSV-Export.
	 *
	 * @param float $grams Gramm.
	 */
	public static function to_kg_raw( float $grams ): string {
		return number_format( $grams / 1000, 3, '.', '' );
	}
}
