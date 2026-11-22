<?php
/**
 * Mengenmeldung.
 *
 * @package CatCodePackagingReport
 *
 * @var array  $rows   Zeilen je Materialart.
 * @var int    $orders Anzahl Bestellungen im Zeitraum.
 * @var string $from   Datum von.
 * @var string $to     Datum bis.
 */

defined( 'ABSPATH' ) || exit;

$ccvp_total  = 0.0;

foreach ( $rows as $ccvp_row ) {
	$ccvp_total += (float) $ccvp_row['grams'];
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Packaging report', 'catcode-packaging-report' ); ?></h1>


	<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
		<input type="hidden" name="page" value="ccvp-report" />
		<label for="ccvp_from"><?php esc_html_e( 'From', 'catcode-packaging-report' ); ?></label>
		<input type="date" id="ccvp_from" name="from" value="<?php echo esc_attr( $from ); ?>" />
		<label for="ccvp_to"><?php esc_html_e( 'To', 'catcode-packaging-report' ); ?></label>
		<input type="date" id="ccvp_to" name="to" value="<?php echo esc_attr( $to ); ?>" />
		<?php submit_button( __( 'Show', 'catcode-packaging-report' ), 'secondary', '', false ); ?>
	</form>

	<p class="description">
		<?php esc_html_e( 'The report goes back as far as the previous month.', 'catcode-packaging-report' ); ?>
	</p>

	<p>
		<?php
		printf(
			/* translators: 1: period start date, 2: period end date, 3: number of orders. */
			esc_html__( 'Period %1$s to %2$s, %3$d orders recorded.', 'catcode-packaging-report' ),
			esc_html( $from ),
			esc_html( $to ),
			(int) $orders
		);
		?>
	</p>

	<table class="widefat striped" style="max-width:640px">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Material type', 'catcode-packaging-report' ); ?></th>
				<th style="text-align:right"><?php esc_html_e( 'Quantity in kg', 'catcode-packaging-report' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $rows as $ccvp_row ) : ?>
				<tr>
					<td><?php echo esc_html( (string) $ccvp_row['label'] ); ?></td>
					<td style="text-align:right"><?php echo esc_html( (string) $ccvp_row['kg'] ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( $ccvp_total <= 0 ) : ?>
		<p class="description">
			<?php esc_html_e( 'Nothing is recorded for this period. Recording starts from the moment the plugin is active, so orders placed before that are not included.', 'catcode-packaging-report' ); ?>
		</p>
	<?php endif; ?>


	<p class="description" style="max-width:640px">
		<?php esc_html_e( 'These figures go into LUCID and into your dual system exactly as they stand here: kilograms with three decimals per material type. Once recorded, a figure no longer changes, even if you switch to a different box later on.', 'catcode-packaging-report' ); ?>
	</p>
</div>
