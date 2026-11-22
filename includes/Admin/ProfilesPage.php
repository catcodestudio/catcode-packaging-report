<?php
/**
 * Verwaltung der Verpackungsprofile.
 *
 * @package CatCodePackagingReport
 */

namespace CatCode\PackagingReport\Admin;

use CatCode\PackagingReport\Core\Assignment;
use CatCode\PackagingReport\Core\Materials;
use CatCode\PackagingReport\Core\Profiles;

defined( 'ABSPATH' ) || exit;

/**
 * Seite „Verpackung“ unter WooCommerce.
 */
final class ProfilesPage {

	public const SLUG = 'ccvp-profiles';

	/**
	 * Hooks.
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 60 );
		add_action( 'admin_post_ccvp_save_profile', array( __CLASS__, 'handle_save' ) );
		add_action( 'admin_post_ccvp_delete_profile', array( __CLASS__, 'handle_delete' ) );
		add_action( 'admin_post_ccvp_save_defaults', array( __CLASS__, 'handle_defaults' ) );
	}

	/**
	 * Menüeintrag.
	 */
	public static function menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'Packaging profiles', 'catcode-packaging-report' ),
			__( 'Packaging', 'catcode-packaging-report' ),
			'manage_woocommerce',
			self::SLUG,
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Seite.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$profiles = Profiles::all();

		include CCVP_DIR . 'templates/profiles.php';
	}

	/**
	 * Profil speichern.
	 */
	public static function handle_save(): void {
		self::guard( 'ccvp_save_profile' );

		// phpcs:ignore WordPress.Security.NonceVerification -- guard() ran check_admin_referer() before this line.
		$id   = isset( $_POST['profile_id'] ) ? sanitize_key( wp_unslash( (string) $_POST['profile_id'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification -- guard() ran check_admin_referer() before this line.
		$name = isset( $_POST['profile_name'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['profile_name'] ) ) : '';

		$materials = array();

		foreach ( Materials::all() as $code => $unused ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- guard() ran check_admin_referer() before this loop.
			$raw = isset( $_POST['grams'][ $code ] ) ? sanitize_text_field( wp_unslash( (string) $_POST['grams'][ $code ] ) ) : '';
			// Deutsche Eingabe: „12,5“ ist hier normal und darf nicht zu 12 werden.
			$materials[ $code ] = (float) str_replace( ',', '.', $raw );
		}

		Profiles::save( $id, $name, $materials );
		self::back( 'saved' );
	}

	/**
	 * Profil löschen.
	 */
	public static function handle_delete(): void {
		self::guard( 'ccvp_delete_profile' );

		// phpcs:ignore WordPress.Security.NonceVerification -- guard() ran check_admin_referer() before this line.
		$id = isset( $_GET['profile_id'] ) ? sanitize_key( wp_unslash( (string) $_GET['profile_id'] ) ) : '';

		if ( $id !== '' ) {
			Profiles::delete( $id );
		}

		self::back( 'deleted' );
	}

	/**
	 * Standardprofile speichern.
	 */
	public static function handle_defaults(): void {
		self::guard( 'ccvp_save_defaults' );

		// phpcs:ignore WordPress.Security.NonceVerification -- guard() ran check_admin_referer() before this line.
		$default  = isset( $_POST['default_profile'] ) ? sanitize_key( wp_unslash( (string) $_POST['default_profile'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification -- guard() ran check_admin_referer() before this line.
		$shipping = isset( $_POST['shipping_profile'] ) ? sanitize_key( wp_unslash( (string) $_POST['shipping_profile'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification -- guard() ran check_admin_referer() before this line.
		$per_item = isset( $_POST['shipping_per_item'] ) ? 'yes' : 'no';

		update_option( Assignment::OPT_DEFAULT, Profiles::get( $default ) ? $default : '' );
		update_option( Assignment::OPT_SHIPPING, Profiles::get( $shipping ) ? $shipping : '' );
		update_option( Assignment::OPT_PER_ITEM, $per_item );

		self::back( 'saved' );
	}

	/**
	 * Rechte und Nonce.
	 *
	 * @param string $action Aktion.
	 */
	private static function guard( string $action ): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'catcode-packaging-report' ) );
		}

		check_admin_referer( $action );
	}

	/**
	 * Zurück zur Seite.
	 *
	 * @param string $notice Hinweis-Code.
	 */
	private static function back( string $notice ): void {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'       => self::SLUG,
					'ccvp_notice' => $notice,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
}
