<?php
/**
 * Tabelle für die Momentaufnahmen.
 *
 * @package CatCodePackagingReport
 */

namespace CatCode\PackagingReport\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Eigene Tabelle statt Bestell-Meta: der Bericht summiert über Zeiträume und
 * Materialarten, und das ist auf einer flachen Tabelle eine Abfrage statt
 * eines Scans über alle Bestellungen.
 */
final class Installer {

	public const DB_VERSION = '1';
	public const OPT_DB     = 'ccvp_db_version';

	/**
	 * Tabellenname.
	 */
	public static function table(): string {
		global $wpdb;

		return $wpdb->prefix . 'ccvp_lines';
	}

	/**
	 * Bei Aktivierung.
	 */
	public static function activate(): void {
		self::create_table();
		update_option( self::OPT_DB, self::DB_VERSION, false );
	}

	/**
	 * Still nachziehen, wenn per FTP aktualisiert wurde.
	 */
	public static function maybe_upgrade(): void {
		if ( (string) get_option( self::OPT_DB, '' ) === self::DB_VERSION ) {
			return;
		}

		self::create_table();
		update_option( self::OPT_DB, self::DB_VERSION, false );
	}

	/**
	 * Tabelle anlegen.
	 */
	private static function create_table(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table();
		$collate = $wpdb->get_charset_collate();

		// `recorded_at` ist bewusst das Datum der Bestellung und nicht das der
		// Zeile: die Meldung geht nach Bestellzeitpunkt, auch wenn wir Monate
		// später nacherfassen.
		dbDelta(
			"CREATE TABLE {$table} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				order_id BIGINT UNSIGNED NOT NULL,
				material VARCHAR(32) NOT NULL,
				grams DECIMAL(12,3) NOT NULL DEFAULT 0,
				source VARCHAR(16) NOT NULL DEFAULT 'product',
				recorded_at DATETIME NOT NULL,
				PRIMARY KEY (id),
				KEY order_id (order_id),
				KEY recorded_at (recorded_at),
				KEY material_at (material, recorded_at)
			) {$collate};"
		);
	}
}
