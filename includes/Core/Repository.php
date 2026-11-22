<?php
/**
 * Lesen und Schreiben der erfassten Mengen.
 *
 * @package CatCodePackagingReport
 */

namespace CatCode\PackagingReport\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Zugriff auf die Zeilentabelle.
 */
final class Repository {

	/**
	 * Zeilen einer Bestellung ersetzen.
	 *
	 * @param int                 $order_id    Bestell-ID.
	 * @param array<string,float> $grams       Gramm je Materialcode.
	 * @param string              $source      'product' oder 'shipping'.
	 * @param string              $recorded_at MySQL-Datum der Bestellung.
	 */
	public static function replace( int $order_id, array $grams, string $source, string $recorded_at ): void {
		global $wpdb;

		$table = Installer::table();

		$wpdb->delete( $table, array( 'order_id' => $order_id, 'source' => $source ), array( '%d', '%s' ) ); // phpcs:ignore WordPress.DB

		foreach ( $grams as $material => $value ) {
			$value = round( (float) $value, 3 );

			if ( $value <= 0 ) {
				continue;
			}

			$wpdb->insert( // phpcs:ignore WordPress.DB
				$table,
				array(
					'order_id'    => $order_id,
					'material'    => (string) $material,
					'grams'       => $value,
					'source'      => $source,
					'recorded_at' => $recorded_at,
				),
				array( '%d', '%s', '%f', '%s', '%s' )
			);
		}
	}

	/**
	 * Alle Zeilen einer Bestellung entfernen.
	 *
	 * @param int $order_id Bestell-ID.
	 */
	public static function forget( int $order_id ): void {
		global $wpdb;

		$wpdb->delete( Installer::table(), array( 'order_id' => $order_id ), array( '%d' ) ); // phpcs:ignore WordPress.DB
	}

	/**
	 * Summen je Materialart in einem Zeitraum.
	 *
	 * @param string $from Datum von (Y-m-d).
	 * @param string $to   Datum bis (Y-m-d), einschließlich.
	 * @return array<string,float>
	 */
	public static function totals( string $from, string $to ): array {
		global $wpdb;

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB
			$wpdb->prepare(
				"SELECT material, SUM(grams) AS total FROM {$wpdb->prefix}ccvp_lines WHERE recorded_at >= %s AND recorded_at <= %s GROUP BY material", // phpcs:ignore WordPress.DB.PreparedSQL
				$from . ' 00:00:00',
				$to . ' 23:59:59'
			),
			ARRAY_A
		);

		$out = array();

		foreach ( (array) $rows as $row ) {
			$out[ (string) $row['material'] ] = (float) $row['total'];
		}

		return $out;
	}

	/**
	 * Wie viele Bestellungen stecken im Zeitraum — damit man sieht, ob die
	 * Zahl überhaupt plausibel ist, bevor man sie meldet.
	 *
	 * @param string $from Datum von.
	 * @param string $to   Datum bis.
	 */
	public static function order_count( string $from, string $to ): int {
		global $wpdb;

		return (int) $wpdb->get_var( // phpcs:ignore WordPress.DB
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT order_id) FROM {$wpdb->prefix}ccvp_lines WHERE recorded_at >= %s AND recorded_at <= %s", // phpcs:ignore WordPress.DB.PreparedSQL
				$from . ' 00:00:00',
				$to . ' 23:59:59'
			)
		);
	}

	/**
	 * Ist die Bestellung schon erfasst?
	 *
	 * @param int $order_id Bestell-ID.
	 */
	public static function has( int $order_id ): bool {
		global $wpdb;

		return (bool) $wpdb->get_var( // phpcs:ignore WordPress.DB
			$wpdb->prepare( "SELECT 1 FROM {$wpdb->prefix}ccvp_lines WHERE order_id = %d LIMIT 1", $order_id ) // phpcs:ignore WordPress.DB.PreparedSQL
		);
	}
}
