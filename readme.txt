=== CatCode Packaging Report ===
Contributors: catcodestudio
Tags: verpackungsgesetz, verpackung, woocommerce, packaging, lucid
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Records the packaging weight of every WooCommerce order by material type and turns it into a report you read instead of rebuild.

== Description ==

German shops that ship goods have to report how much packaging they put into circulation: grams of paper, plastic, glass and the rest, per material type, for the year. The numbers exist in the shop already — they are just spread across products, boxes and orders, and most shops end up reconstructing them in a spreadsheet in January.

This plugin records them as they happen.

You describe your packaging once as a **profile**: a name and the weight in grams for each material type, for example "box M with tape" = 180 g cardboard + 6 g plastic. Products point at a profile, the shipment has its own profile for the box and filling material, and every paid order stores what it actually used.

The report screen then shows kilograms per material type for any period you pick, together with the number of orders behind the figure, so the total can be sanity-checked before it is filed.

= Why a snapshot per order =

The weight is written to the order once, when it is paid. If you switch to a lighter box next year, last year's figures do not silently change underneath you — which matters, because the report you already filed has to stay reproducible.

= Material types =

The eight types the German report asks for: glass; paper, board and cardboard; ferrous metals; aluminium; beverage carton packaging; other composite packaging; plastics; other materials. Types with no packaging are shown as zero, because the form asks for all of them and a zero is an answer.

= What it does not do =

It does not send anything anywhere. There is no machine interface for filing this report, so the plugin gives you the figures and you enter them yourself in the register and with your scheme operator. Nothing leaves your site — the plugin makes no external requests at all.

= Not legal advice =

The plugin computes what you told it about your packaging. Whether you are obliged to register, and which figures you have to file, is between you and your scheme operator.

== Installation ==

1. Install and activate the plugin. WooCommerce has to be active.
2. Go to WooCommerce → Packaging and create a packaging profile with the grams per material type.
3. Set the default sales packaging and the shipment packaging on the same screen.
4. Optionally pick a different profile per product, on the product's Shipping tab.
5. Read the figures under WooCommerce → Packaging report.

Recording starts from the moment the plugin is active. Orders placed before that are not in the report.

== Frequently Asked Questions ==

= Where do the grams come from? =

From you. Weigh a box with its tape and filling once, write the numbers into a profile, and every order that uses that profile inherits them.

= A product ships in its own box, not in ours. What then? =

Give that product its own profile with the real packaging, and it stops inheriting the default one.

= We ship several items in one box. Is the box counted once? =

Yes, once per order by default. If you send bulky goods separately, there is a switch that counts the shipment packaging per line item instead.

= Which orders are counted? =

Orders that reach processing or completed. A refund does not remove the packaging you already used, so it is left in the figure; a cancelled or deleted order is taken out.

= How far back can the report go? =

As far as the recorded data goes. Pick any from/to dates on the report screen.

= Does it talk to LUCID or to my scheme operator? =

No. Neither offers a machine interface for this, so the plugin only prepares the figures.

== Screenshots ==

1. Packaging profiles with the weight per material type, and the default assignment below them.
2. The packaging profile selector on the product's Shipping tab.
3. The report: kilograms per material type for the selected period.

== Changelog ==

= 1.0.2 =
* Fixed: packaging of paid orders was not recorded at all (the database table name was missing), so the report stayed empty.
* Cancelled and deleted orders are removed from the report instead of staying in the total.
* The default period (last month) now follows the shop time zone instead of UTC.

= 1.0.1 =
* Removed the packaging profile limit: any number of profiles can be created.
* Removed the report period restriction: any date range can be selected.
* Explicit nonce and capability check when saving the packaging profile on a product.

= 1.0.0 =
* First release: packaging profiles, per-product assignment, shipment packaging, per-order snapshot on payment, report by material type for a period.
