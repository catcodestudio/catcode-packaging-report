<?php
/**
 * Deinstallation.
 *
 * @package CatCodePackagingReport
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// Die erfassten Mengen sind Buchhaltung: der Händler muss sie im Zweifel Jahre
// später noch vorzeigen können. Deshalb löschen wir bei der Deinstallation nur
// die Einstellungen, nicht die Tabelle mit den Zeilen.
delete_option( 'ccvp_settings' );
delete_option( 'ccvp_profiles' );
delete_option( 'ccvp_default_profile' );
delete_option( 'ccvp_shipping_profile' );
delete_option( 'ccvp_shipping_per_item' );
delete_option( 'ccvp_db_version' );
