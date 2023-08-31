<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;   //Exit if accessed directly.
} 
?>
<div class="wrap">
	<h1>
        <?php esc_html_e('Multi Currency' , 'xs-mcs') ?>
        <a class="xs-mcs-pro-link" href="https://codecanyon.net/item/advance-woocommerce-multi-currency-switcher/29504917" target="_blank">
            <div class="xs-mcs-button-main">
                <?php submit_button(esc_html__("Pro Version" , 'xs-mcs' ), 'secondary' , "xs-mcs-button"); ?>
            </div>
        </a>
    </h1>
	<h2>
		<?php esc_html_e('Multi Currency Settings', 'xs-mcs'); ?>	
	</h2>
	<div class="xs-mcs-form-wrap">
		<div class="xs-mcs-form-inner">
			<form class="xs-mcs-form xs-mcs-options-form" action="<?php echo admin_url('admin.php?page=xs-mcs-options'); ?>" method="POST">
				<?php wp_nonce_field( 'xs-mcs-save-settings', 'xs-mcs-save-settings-nounce' ); ?>
				<input type="hidden" name="action" value="xs_mcs_save_options" />
				<?php foreach($this->xsmcs->XSMCS_Options->config_options() as $options_id => $options){ 
					?>
					<h3><?php echo $options['label'] ?></h3>
					<table id="<?php echo $options_id; ?>" class="form-table">
						<tbody>
							<?php foreach( $options['options'] as $option_id => $option ): ?>
								<?php $this->xsmcs->XSMCS_Options->render_option_html($option, $option_id); ?>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php } ?>
				<div class="xs-mcs-submit">
					<input type="submit" class="button button-primary" name="save_options" value="<?php esc_html_e('Save Options', 'xs-mcs'); ?>"/>
					<span class="xs-mcs-spinner spinner"></span>
				</div>
			</form>
		</div>
	</div>		
</div>