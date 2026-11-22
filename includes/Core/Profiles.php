<?php
/**
 * Verpackungsprofile.
 *
 * @package CatCodePackagingReport
 */

namespace CatCode\PackagingReport\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Ein Profil ist eine Verpackung mit Gewicht je Materialart: „Karton M“ =
 * 320 g Papier + 12 g Kunststoff (Klebeband). Produkte, Kategorien und
 * Versandarten zeigen auf ein Profil, nicht auf einzelne Gramm-Werte —
 * sonst muss man beim Wechsel des Kartons hunderte Produkte anfassen.
 */
final class Profiles {

	public const OPTION = 'ccvp_profiles';

	/**
	 * Alle Profile.
	 *
	 * @return array<string,array{name:string,materials:array<string,float>}>
	 */
	public static function all(): array {
		$raw = get_option( self::OPTION, array() );

		return is_array( $raw ) ? $raw : array();
	}

	/**
	 * Ein Profil.
	 *
	 * @param string $id Profil-ID.
	 * @return array{name:string,materials:array<string,float>}|null
	 */
	public static function get( string $id ): ?array {
		$all = self::all();

		return $all[ $id ] ?? null;
	}

	/**
	 * Profil speichern.
	 *
	 * @param string               $id        Profil-ID, leer = neues Profil.
	 * @param string               $name      Anzeigename.
	 * @param array<string,float>  $materials Gramm je Materialcode.
	 */
	public static function save( string $id, string $name, array $materials ): string {
		$all   = self::all();
		$id    = $id !== '' ? $id : 'p' . substr( md5( $name . microtime( true ) ), 0, 8 );
		$clean = array();

		foreach ( $materials as $code => $grams ) {
			$grams = (float) $grams;

			// Null-Zeilen nicht speichern: sie blähen den Bericht auf und
			// verschleiern, welche Materialien wirklich anfallen.
			if ( ! Materials::exists( (string) $code ) || $grams <= 0 ) {
				continue;
			}

			$clean[ (string) $code ] = round( $grams, 3 );
		}

		$all[ $id ] = array(
			'name'      => $name !== '' ? $name : $id,
			'materials' => $clean,
		);

		update_option( self::OPTION, $all, false );

		return $id;
	}

	/**
	 * Profil löschen.
	 *
	 * @param string $id Profil-ID.
	 */
	public static function delete( string $id ): void {
		$all = self::all();
		unset( $all[ $id ] );
		update_option( self::OPTION, $all, false );
	}

	/**
	 * Auswahlliste für Selects.
	 *
	 * @return array<string,string>
	 */
	public static function options(): array {
		$out = array();

		foreach ( self::all() as $id => $profile ) {
			$out[ $id ] = (string) $profile['name'];
		}

		return $out;
	}
}
