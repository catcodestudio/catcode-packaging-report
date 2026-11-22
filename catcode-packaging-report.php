<?php
/**
 * Plugin Name: CatCode Packaging Report
 * Plugin URI: https://catcode.com.ua/modules/packaging-report-woocommerce/
 * Description: Records the packaging weight of every WooCommerce order by LUCID material type and turns it into the quantity report German shops file under the Packaging Act (VerpackG).
 * Version: 1.0.2
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Author: CatCode
 * Author URI: https://catcode.com.ua
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: catcode-packaging-report
 * Domain Path: /languages
 * WC requires at least: 6.0
 * WC tested up to: 10.7
 *
 * @package CatCodePackagingReport
 */

defined( 'ABSPATH' ) || exit;

defined( 'CCVP_VERSION' ) || define( 'CCVP_VERSION', '1.0.2' );
defined( 'CCVP_FILE' ) || define( 'CCVP_FILE', __FILE__ );
defined( 'CCVP_DIR' ) || define( 'CCVP_DIR', plugin_dir_path( __FILE__ ) );
defined( 'CCVP_URL' ) || define( 'CCVP_URL', plugin_dir_url( __FILE__ ) );
defined( 'CCVP_BASENAME' ) || define( 'CCVP_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Explicit, dependency-ordered includes.
 *
 * @var string[] $ccvp_includes
 */
$ccvp_includes = array(
	'includes/Core/Materials.php',
	'includes/Core/Profiles.php',
	'includes/Core/Assignment.php',
	'includes/Core/Installer.php',
	'includes/Core/Repository.php',
	'includes/Core/Recorder.php',
	'includes/Core/Report.php',
	'includes/Admin/ProfilesPage.php',
	'includes/Admin/ReportPage.php',
	'includes/Admin/ProductFields.php',
);

foreach ( $ccvp_includes as $ccvp_inc ) {
	require_once CCVP_DIR . $ccvp_inc;
}

unset( $ccvp_includes, $ccvp_inc );

register_activation_hook(
	__FILE__,
	static function () {
		\CatCode\PackagingReport\Core\Installer::activate();
	}
);

add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', CCVP_FILE, true );
		}
	}
);

add_action(
	'plugins_loaded',
	static function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-error"><p>' .
						esc_html__( 'CatCode Packaging Report requires WooCommerce.', 'catcode-packaging-report' ) .
						'</p></div>';
				}
			);

			return;
		}

		// Die Datenbanktabelle kann bei einem Update über FTP fehlen — Aktivierungs-
		// Hook läuft dann nicht. Deshalb hier still nachziehen.
		\CatCode\PackagingReport\Core\Installer::maybe_upgrade();

		\CatCode\PackagingReport\Core\Recorder::init();

		if ( is_admin() ) {
			\CatCode\PackagingReport\Admin\ProfilesPage::init();
			\CatCode\PackagingReport\Admin\ReportPage::init();
			\CatCode\PackagingReport\Admin\ProductFields::init();
		}
	}
);
