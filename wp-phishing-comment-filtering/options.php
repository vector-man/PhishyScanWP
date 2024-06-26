<?php defined( 'ABSPATH' ) or die; ?>
<div class="wrap wrap-file-library-widget">
	<?php settings_errors(); ?>
	<h1><?php _e( 'WP Phishing Comment Filtering - Options', 'wp-phishing-comment-filtering' ); ?></h1>
	<form method="post" action="options.php">
		<?php settings_fields( WP_PHISHING_COMMENT_FILTERING_OPTSGROUP_NAME ); ?>
		<?php do_settings_sections( WP_PHISHING_COMMENT_FILTERING_OPTSGROUP_NAME ); ?>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'API Key', 'wp-phishing-comment-filtering' ); ?></th>
				<td>
					<input type="text" class="regular-text" name="<?php echo esc_attr( WP_PHISHING_COMMENT_FILTERING_OPTIONS_NAME ); ?>[api_key]" value="<?php esc_attr_e( $this->get_option( 'api_key' ) ); ?>">
				</td>
			</tr>
			<!--tr>
				<th><?php esc_html_e( 'Detection Level', 'wp-phishing-comment-filtering' ); ?></th>
				<td>
					<?php $detection_level = $this->get_option( 'detection_level', 'warn' ); ?>
					<select name="<?php echo esc_attr( WP_PHISHING_COMMENT_FILTERING_OPTIONS_NAME ); ?>[detection_level]">
						<option value="warn" <?php echo esc_attr( $detection_level == 'warn' ? 'selected' : '' ); ?>><?php esc_attr_e( 'Warn', 'wp-phishing-comment-filtering' ); ?></option>
						<option value="fail" <?php echo esc_attr( $detection_level == 'fail' ? 'selected' : '' ); ?>><?php esc_attr_e( 'Fail', 'wp-phishing-comment-filtering' ); ?></option>
					</select>
				</td>
			</tr-->
		</table>
		<?php submit_button(); ?>
	</form>
	<p><?php echo sprintf( esc_html__( '%1$sGet API Key%2$s', 'wp-phishing-comment-filtering' ), '<a target="_blank" href="' . esc_attr( WP_PHISHING_COMMENT_FILTERING_BUY_URL ) . '">', '</a>' ); ?></p>
	<?php if ( ! empty( null ) ) : ?>
		<?php if ( is_dir( $upload_dir ) && is_writable( $upload_dir ) ) : ?>
			<p><?php esc_html_e( 'Great, your uploads directory is writable!' ); ?></p>
		<?php else : ?>
			<p><?php esc_html_e( 'Error, your uploads directory does not exist or is not writable!' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>
</div>