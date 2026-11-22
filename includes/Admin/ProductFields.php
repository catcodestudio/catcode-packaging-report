<?php
/**
 * Profilauswahl am Produkt und an der Kategorie.
 *
 * @package CatCodePackagingReport
 */

namespace CatCode\PackagingReport\Admin;

use CatCode\PackagingReport\Core\Assignment;
use CatCode\PackagingReport\Core\Profiles;

defined( 'ABSPATH' ) || exit;

/**
 * Ein Select im Reiter „Versand“ des Produkts — dort sucht man Verpackung,
 * nicht in einem eigenen Menüpunkt.
 */
final class ProductFields {

	/**
	 * Hooks.
	 */
	public static function init(): void {
		add_action( 'woocommerce_product_options_shipping_product_data', array( __CLASS__, 'field' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save' ) );

	}

	/**
	 * Produktfeld.
	 */
	public static function field(): void {
		$options = array( '' => __( '— Default profile —', 'catcode-packaging-report' ) ) + Profiles::options();

		woocommerce_wp_select(
			array(
				'id'          => Assignment::META_PRODUCT,
				'label'       => __( 'Packaging profile', 'catcode-packaging-report' ),
				'options'     => $options,
				'desc_tip'    => true,
				'description' => __( 'Sales packaging of this item. With nothing selected the default profile applies.', 'catcode-packaging-report' ),
			)
		);
	}

	/**
	 * Produktfeld speichern.
	 *
	 * @param \WC_Product $product Produkt.
	 */
	public static function save( $product ): void {
		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		if ( ! isset( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_product', $product->get_id() ) ) {
			return;
		}

		$value = isset( $_POST[ Assignment::META_PRODUCT ] ) ? sanitize_text_field( wp_unslash( (string) $_POST[ Assignment::META_PRODUCT ] ) ) : '';

		$product->update_meta_data( Assignment::META_PRODUCT, Profiles::get( $value ) ? $value : '' );
	}


}
