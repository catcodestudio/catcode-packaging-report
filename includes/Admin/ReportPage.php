<?php
/**
 * Die Mengenmeldung als Seite und als CSV.
 *
 * @package CatCodePackagingReport
 */

namespace CatCode\PackagingReport\Admin;

use CatCode\PackagingReport\Core\Recorder;
use CatCode\PackagingReport\Core\Report;
use CatCode\PackagingReport\Core\Repository;

defined( 'ABSPATH' ) || exit;

/**
 * Seite „Mengenmeldung“.
 */
final class ReportPage {

	public const SLUG = 'ccvp-report';

	/**
	 * Hooks.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 61 );
	}

	/**
	 * Menüeintrag.
	 */
	public static function menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Packaging report', 'catcode-packaging-report' ),
			__( 'Packaging report', 'catcode-packaging-report' ),
			'manage_woocommerce',
			self::SLUG,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Zeitraum aus der Anfrage, mit Vormonat als Vorgabe.
	 *
	 * @return array{0:string,1:string}
	 */
	public static function period(): array {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nur Leseansicht mit Datumsfiltern.
		$from = isset( $_REQUEST['from'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['from'] ) ) : '';
		$to   = isset( $_REQUEST['to'] ) ? sanitize_text_field( wp_unslash( (string) $_REQUEST['to'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		$valid = static function ( string $date ): bool {
			return (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date );
		};

		if ( ! $valid( $from ) || ! $valid( $to ) ) {
			// Vormonat in der Zeitzone des Shops, nicht in UTC — sonst springt die
			// Vorgabe in den ersten Stunden eines Monats um einen Monat zu weit zurück.
			$last = current_datetime()->modify( 'first day of last month' );
			$from = $last->format( 'Y-m-01' );
			$to   = $last->format( 'Y-m-t' );
		}

		if ( $from > $to ) {
			list( $from, $to ) = array( $to, $from );
		}

		return array( $from, $to );
	}

	/**
	 * Seite.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		list( $from, $to ) = self::period();

		$rows   = Report::rows( $from, $to );
		$orders = Repository::order_count( $from, $to );

		include CCVP_DIR . 'templates/report.php';
	}


}
