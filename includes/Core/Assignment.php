<?php
/**
 * Welches Profil gilt für welche Position.
 *
 * @package CatCodePackagingReport
 */

namespace CatCode\PackagingReport\Core;


defined( 'ABSPATH' ) || exit;

/**
 * Auflösung von Produkt → Verkaufsverpackung und Bestellung → Versandverpackung.
 */
final class Assignment {

	public const META_PRODUCT   = '_ccvp_profile';
	public const TERM_META      = 'ccvp_profile';
	public const OPT_DEFAULT    = 'ccvp_default_profile';
	public const OPT_SHIPPING   = 'ccvp_shipping_profile';
	public const OPT_PER_ITEM   = 'ccvp_shipping_per_item';

	/**
	 * Profil für ein Produkt: Produkt → Kategorie → Standard.
	 *
	 * Varianten erben vom Elternprodukt, weil die Verpackung praktisch nie an
	 * der Farbe hängt; wer es doch braucht, setzt es an der Variante selbst.
	 *
	 * @param \WC_Product|null $product Produkt.
	 */
	public static function for_product( ?\WC_Product $product ): ?array {
		if ( ! $product ) {
			return self::fallback();
		}

		$own = (string) $product->get_meta( self::META_PRODUCT, true );

		if ( $own !== '' && Profiles::get( $own ) ) {
			return Profiles::get( $own );
		}

		$parent_id = $product->get_parent_id();

		if ( $parent_id ) {
			$parent = wc_get_product( $parent_id );

			if ( $parent ) {
				$inherited = (string) $parent->get_meta( self::META_PRODUCT, true );

				if ( $inherited !== '' && Profiles::get( $inherited ) ) {
					return Profiles::get( $inherited );
				}
			}
		}


		return self::fallback();
	}


	/**
	 * Standardprofil für Verkaufsverpackung.
	 */
	public static function fallback(): ?array {
		$id = (string) get_option( self::OPT_DEFAULT, '' );

		return $id !== '' ? Profiles::get( $id ) : null;
	}

	/**
	 * Versandverpackung der Bestellung (Karton, Füllmaterial, Klebeband).
	 */
	public static function shipping_profile(): ?array {
		$id = (string) get_option( self::OPT_SHIPPING, '' );

		return $id !== '' ? Profiles::get( $id ) : null;
	}

	/**
	 * Versandverpackung je Position statt je Bestellung?
	 *
	 * Wer sperrige Ware einzeln verschickt, packt pro Position einen Karton —
	 * eine Bestellung mit drei Artikeln ergibt dann drei Kartons, nicht einen.
	 */
	public static function shipping_per_item(): bool {
		return get_option( self::OPT_PER_ITEM, 'no' ) === 'yes';
	}
}
