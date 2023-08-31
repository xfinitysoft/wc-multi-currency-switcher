<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;   //Exit if accessed directly.
}

if( isset( $response ) and is_array( $response ) ){
	?>
	<div class="notice <?php if($response['success']){echo 'notice-success'; }else{ echo 'notice-error'; } ?> is-dismissible">
		<p><?php echo $response['message']; ?></p>
	</div>

	<?php
}
?>
<div class="wrap">
	<h2>
		<?php 
			if( $this->action == 'new' ){ 
				esc_html_e('Add New Currency', 'xs-mcs');
			}else{
				esc_html_e('Edit Currency', 'xs-mcs');
			}
		?>	
	</h2>
	<div class="xs-mcs-form-wrap">
		<div class="xs-mcs-form-inner">
			<form class="xs-mcs-form" action="<?php echo admin_url('admin.php?page=xs-mcs-currencies'); ?>" method="POST">
				<?php 
					if( $this->action == 'new' ){ 
						wp_nonce_field( 'add_new_currency', 'add_new_currency_nounce' );
					}else{
						wp_nonce_field( 'edit_currency', 'edit_currency_nounce' );
						echo '<input type="hidden" name="previous_currency" value="'.$currency['name'].'">';
					}
				?>
				<table class="form-table">
					<tbody>
						<tr>
							<th valign="top"><?php esc_html_e('Currency', 'xs-mcs'); ?>:</th>
							<td>
								<select name="currency[name]" required>
									<option value=""><?php esc_html_e( 'Select Currency', 'xs-mcs' ); ?></option>
									<?php
									if( isset( $wc_currencies ) && is_array( $wc_currencies ) ){ 
										foreach($wc_currencies as $c_code => $c_name): ?>
											<option value="<?php echo $c_code; ?>" <?php selected(strtoupper($currency['name']), $c_code ); ?>><?php echo $c_name; ?></option>
										<?php endforeach; 
									}	?>
								</select>
							</td>
						</tr>
						
						<tr>
							<th valign="top">
								<?php esc_html_e('Value', 'xs-mcs'); ?>:
								<p class="xs-mcs-desc" <?php if( !$currency['value'] ){ echo 'style="display:hidden"';} ?>>
									<span class="xs-mcs-base-curr-txt"><?php echo get_woocommerce_currency(); ?></span> <?php esc_html_e('to', 'xs-mcs'); ?> <span class="xs-mcs-target-curr-txt"><?php echo strtoupper($currency['name']);?></span>
								</p>
							</th>
							<td>
								<input step="any" min="0" type="number" name="currency[value]" value="<?php echo $currency['value']; ?>" />
								<a id="xs-mcs-get-value-btn" class="button button-primary" ><?php esc_html_e( 'Get Value',  'xs-mcs'); ?></a>
								<span class="xs-mcs-spinner spinner"></span>
								<span class="xs-mcs-desc xs-mcs-last-updated-text">
									<?php
										if( isset($currency['updated_on']) and $currency['updated_on'] ){
											$date_format = get_option('date_format');
											$time_format = get_option('time_format');
											$gmt_ofset = get_option('gmt_offset')*60*60;
											$updated_text = date($date_format.' '.$time_format, $currency['updated_on']+$gmt_ofset);
											if(isset($currency['manualy_updated']) and $currency['manualy_updated'] == 'yes'){
												echo esc_html__('Manualy updated on', 'xs-mcs').' '. $updated_text;
											}else if($currency['auto_update'] == 'yes'){
												echo esc_html__('Auto updated on', 'xs-mcs').' '. $updated_text;
											}
										}
									?>
									
								</span>
								<p class="xs-mcs-desc"><?php esc_html_e('You can set currency value manualy or you can get currency value online by clicking  Get Value button from latest forex rates from', 'xs-mcs'); ?> <a href="http://currencyconverterapi.com">Currency Converter API</a>.</p>
							</td>
						</tr>
						
						<tr>
							<th valign="top"><?php esc_html_e('Exchange Fee', 'xs-mcs'); ?>:</th>
							<td>
								<input step="any" min="0" type="number" name="currency[exchange_fee][value]" value="<?php echo $currency['exchange_fee']['value']; ?>" />
								<select name="currency[exchange_fee][type]">
									<option value="percentage" <?php echo  (isset($currency['exchange_fee']['type']) && $currency['exchange_fee']['type']=='percentage') ? "selected":'' ; ?>><?php esc_html_e('Percentage', 'xs-mcs'); ?></option>
									<option value="flat" <?php echo  (isset($currency['exchange_fee']['type']) && $currency['exchange_fee']['type']=='flat') ? "selected":'' ; ?>><?php esc_html_e('Flat', 'xs-mcs'); ?></option>
								</select>
								<a id="xs-mcs-get-value-inc_ex-fee-btn" class="button button-primary" ><?php esc_html_e( 'Get Value Included Exchange Fee',  'xs-mcs'); ?></a>
								<span class="xs-mcs-spinner spinner"></span>
								<p class="xs-mcs-desc"><?php esc_html_e('You can add exchange fee for this currency. This fee will be shown in cart totals and will be calculated on cart total amount. You can calulate currency value including exchange fee by clicking GET Value Included Exchange Fee button.', 'xs-mcs'); ?></p>
							</td>
						</tr>
						
						<tr>
							<th valign="top"><?php esc_html_e('Enable', 'xs-mcs'); ?>:</th>
							<td>
								<input type="checkbox" name="currency[enable]" value="publish" <?php checked($currency['enable'], 'publish', true); ?> />
								<p class="xs-mcs-desc"><?php esc_html_e('If this checkbox is unchecked then this currency will not be shown on frontend.', 'xs-mcs'); ?></p>
							</td>
						</tr>
						<tr class="xs-mcs-submit">
							<td colspan="2">
								<input type="submit" class="button button-primary" name="save_currency" value="<?php esc_html_e('Save Currency', 'xs-mcs'); ?>"/>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
	</div>
</div>