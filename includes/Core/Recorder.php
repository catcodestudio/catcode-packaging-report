<?php
/**
 * Erfassung der Mengen an der Bestellung.
 *
 * @package CatCodePackagingReport
 */

namespace CatCode\PackagingReport\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Schreibt beim Bezahlen einmal fest, wie die Bestellung verpackt war.
 *
 * Bewusst eine Momentaufnahme: wenn der Händler nächstes Jahr auf einen
 * leichteren Karton wechselt, darf sich die bereits gemeldete Menge des
 * Vorjahres nicht rückwirkend ändern.
 */
final class Recorder {

	/**
	 * Hooks.
	 */
	public static function init(): void {
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'record' ), 20 );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'record' ), 20 );
		add_action( 'ccvp_record_order', array( __CLASS__, 'record' ) );

		// Storniert heißt: nicht verschickt, also auch keine Verpackung in Verkehr gebracht.
		// Eine gelöschte Bestellung darf ebenfalls nicht in der Meldung bleiben.
		add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'forget' ) );
		add_action( 'woocommerce_before_delete_order', array( __CLASS__, 'forget' ) );
	}

	/**
	 * Bestellung erfassen.
	 *
	 * @param int  $order_id Bestell-ID.
	 * @param bool $force    Auch erfassen, wenn schon Zeilen da sind.
	 */
	public static function record( $order_id, bool $force = false ): void {
		$order_id = (int) $order_id;
		$order    = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		if ( ! $force && Repository::has( $order_id ) ) {
			return;
		}

		$date = $order->get_date_created();
		$when = $date ? $date->date( 'Y-m-d H:i:s' ) : current_time( 'mysql' );

		$product_grams  = array();
		$shipping_units = 0;

		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof \WC_Order_Item_Product ) {
				continue;
			}

			$qty = max( 1, (int) $item->get_quantity() );
			$profile = Assignment::for_product( $item->get_product() );

			$shipping_units += $qty;

			if ( ! $profile ) {
				continue;
			}

			foreach ( (array) $profile['materials'] as $code => $grams ) {
				$product_grams[ $code ] = ( $product_grams[ $code ] ?? 0 ) + ( (float) $grams * $qty );
			}
		}

		Repository::replace( $order_id, $product_grams, 'product', $when );

		$shipping = Assignment::shipping_profile();

		if ( $shipping ) {
			$units          = Assignment::shipping_per_item() ? max( 1, $shipping_units ) : 1;
			$shipping_grams = array();

			foreach ( (array) $shipping['materials'] as $code => $grams ) {
				$shipping_grams[ $code ] = (float) $grams * $units;
			}

			Repository::replace( $order_id, $shipping_grams, 'shipping', $when );
		}
	}

	/**
	 * Erfasste Mengen einer Bestellung verwerfen.
	 *
	 * @param int $order_id Bestell-ID.
	 */
	public static function forget( $order_id ): void {
		Repository::forget( (int) $order_id );
	}
}
