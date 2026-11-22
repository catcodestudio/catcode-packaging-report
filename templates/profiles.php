<?php
/**
 * Verpackungsprofile.
 *
 * @package CatCodePackagingReport
 *
 * @var array $profiles Profile.
 */

use CatCode\PackagingReport\Core\Assignment;
use CatCode\PackagingReport\Core\Materials;
use CatCode\PackagingReport\Core\Profiles;

defined( 'ABSPATH' ) || exit;

$ccvp_notice = isset( $_GET['ccvp_notice'] ) ? sanitize_key( wp_unslash( (string) $_GET['ccvp_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$ccvp_edit   = isset( $_GET['edit'] ) ? sanitize_key( wp_unslash( (string) $_GET['edit'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$ccvp_cur    = $ccvp_edit !== '' ? Profiles::get( $ccvp_edit ) : null;
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Packaging profiles', 'catcode-packaging-report' ); ?></h1>

	<?php if ( 'saved' === $ccvp_notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'catcode-packaging-report' ); ?></p></div>
	<?php elseif ( 'deleted' === $ccvp_notice ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Profile deleted.', 'catcode-packaging-report' ); ?></p></div>
	<?php endif; ?>

	<p class="description">
		<?php esc_html_e( 'A profile is one packaging setup with a weight per material type. Products point at a profile, so when the box changes you edit the profile instead of hundreds of products.', 'catcode-packaging-report' ); ?>
	</p>

	<h2><?php echo $ccvp_cur ? esc_html__( 'Edit profile', 'catcode-packaging-report' ) : esc_html__( 'New profile', 'catcode-packaging-report' ); ?></h2>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'ccvp_save_profile' ); ?>
		<input type="hidden" name="action" value="ccvp_save_profile" />
		<input type="hidden" name="profile_id" value="<?php echo esc_attr( $ccvp_edit ); ?>" />

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="ccvp_name"><?php esc_html_e( 'Name', 'catcode-packaging-report' ); ?></label></th>
				<td>
					<input type="text" class="regular-text" id="ccvp_name" name="profile_name"
						value="<?php echo esc_attr( $ccvp_cur ? (string) $ccvp_cur['name'] : '' ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. box M with tape', 'catcode-packaging-report' ); ?>" required />
				</td>
			</tr>
			<?php foreach ( Materials::all() as $ccvp_code => $ccvp_label ) : ?>
				<tr>
					<th scope="row"><label for="ccvp_g_<?php echo esc_attr( $ccvp_code ); ?>"><?php echo esc_html( $ccvp_label ); ?></label></th>
					<td>
						<input type="text" inputmode="decimal" class="small-text" id="ccvp_g_<?php echo esc_attr( $ccvp_code ); ?>"
							name="grams[<?php echo esc_attr( $ccvp_code ); ?>]"
							value="<?php echo esc_attr( $ccvp_cur && isset( $ccvp_cur['materials'][ $ccvp_code ] ) ? (string) $ccvp_cur['materials'][ $ccvp_code ] : '' ); ?>" />
						<span class="description"><?php esc_html_e( 'Grams', 'catcode-packaging-report' ); ?></span>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>

		<?php submit_button( $ccvp_cur ? __( 'Save profile', 'catcode-packaging-report' ) : __( 'Create profile', 'catcode-packaging-report' ) ); ?>
	</form>

	<h2><?php esc_html_e( 'Existing profiles', 'catcode-packaging-report' ); ?></h2>

	<?php if ( ! $profiles ) : ?>
		<p><?php esc_html_e( 'No profile created yet.', 'catcode-packaging-report' ); ?></p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Name', 'catcode-packaging-report' ); ?></th>
					<th><?php esc_html_e( 'Composition', 'catcode-packaging-report' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $profiles as $ccvp_id => $ccvp_profile ) : ?>
				<tr>
					<td><strong><?php echo esc_html( (string) $ccvp_profile['name'] ); ?></strong></td>
					<td>
						<?php
						$ccvp_parts = array();

						foreach ( (array) $ccvp_profile['materials'] as $ccvp_m => $ccvp_g ) {
							$ccvp_parts[] = Materials::label( (string) $ccvp_m ) . ' ' . (float) $ccvp_g . ' g';
						}

						echo esc_html( $ccvp_parts ? implode( ' · ', $ccvp_parts ) : __( 'empty', 'catcode-packaging-report' ) );
						?>
					</td>
					<td>
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'ccvp-profiles', 'edit' => $ccvp_id ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Edit', 'catcode-packaging-report' ); ?></a>
						|
						<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'ccvp_delete_profile', 'profile_id' => $ccvp_id ), admin_url( 'admin-post.php' ) ), 'ccvp_delete_profile' ) ); ?>"><?php esc_html_e( 'Delete', 'catcode-packaging-report' ); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'Default assignment', 'catcode-packaging-report' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ccvp_save_defaults' ); ?>
			<input type="hidden" name="action" value="ccvp_save_defaults" />
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Default sales packaging', 'catcode-packaging-report' ); ?></th>
					<td>
						<select name="default_profile">
							<option value="">—</option>
							<?php foreach ( Profiles::options() as $ccvp_id => $ccvp_name ) : ?>
								<option value="<?php echo esc_attr( $ccvp_id ); ?>" <?php selected( get_option( Assignment::OPT_DEFAULT, '' ), $ccvp_id ); ?>><?php echo esc_html( $ccvp_name ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Applies to products without a profile of their own.', 'catcode-packaging-report' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Shipment packaging', 'catcode-packaging-report' ); ?></th>
					<td>
						<select name="shipping_profile">
							<option value="">—</option>
							<?php foreach ( Profiles::options() as $ccvp_id => $ccvp_name ) : ?>
								<option value="<?php echo esc_attr( $ccvp_id ); ?>" <?php selected( get_option( Assignment::OPT_SHIPPING, '' ), $ccvp_id ); ?>><?php echo esc_html( $ccvp_name ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Box, filling material and tape of the shipment.', 'catcode-packaging-report' ); ?></p>
						<label style="display:block;margin-top:8px;">
							<input type="checkbox" name="shipping_per_item" value="1" <?php checked( get_option( Assignment::OPT_PER_ITEM, 'no' ), 'yes' ); ?> />
							<?php esc_html_e( 'Count per line item instead of per order (bulky goods ship separately)', 'catcode-packaging-report' ); ?>
						</label>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Save assignment', 'catcode-packaging-report' ) ); ?>
		</form>
	<?php endif; ?>
</div>
