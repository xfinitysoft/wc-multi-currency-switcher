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
	<h1>
        <?php esc_html_e('Multi Currency' , 'xs-mcs') ?>
        <a class="xs-mcs-pro-link" href="https://codecanyon.net/item/advance-woocommerce-multi-currency-switcher/29504917" target="_blank">
            <div class="xs-mcs-button-main">
                <?php submit_button(esc_html__("Pro Version" , 'xs-mcs' ), 'secondary' , "xs-mcs-button"); ?>
            </div>
        </a>
    </h1>
	<h2>
		<?php esc_html_e('All Currencies', 'xs-mcs'); ?>
		<?php
		$args = array(
				'post_type' 		=> 'xs-mcs-currency',
				'post_status'		=> array('draft', 'publish'),
				'posts_per_page'	=> -1
		); 
		$curr_objects = get_posts($args);
		if(count($curr_objects ) < 2){
		?>
		<a href="admin.php?page=xs-mcs-currencies&action=new" id="xs-mcs-add-new-currency" class="xs-mcs-add-new page-title-action"><?php esc_html_e('New Currency', 'xs-mcs'); ?></a>
		<?php } ?>
	</h2>
	<div class="xs-mcs-currencies">
		<div class="xs-mcs-currencies-wrapp">
			<form>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e('Currency', 'xs-mcs'); ?></th>
							<th><?php esc_html_e('Value', 'xs-mcs'); ?></th>
							<th><?php esc_html_e('Status', 'xs-mcs'); ?></th>
							<th><?php esc_html_e('Actions', 'xs-mcs'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($get_currencies as $currency): ?>
							<tr>
								<td><?php echo $wc_currencies[strtoupper($currency['name'])]; ?></td>
								<td><?php echo wc_price($currency['value'], array('currency' => strtoupper($currency['name']))); ?></td>
								<td><?php echo $currency['enable'] == 'publish' ? esc_html__('Enabled', 'xs-mcs') : esc_html__('Disabled', 'xs-mcs'); ?></td>
								<td>
									<a class="button button-primary xs-mcs-edit-currency" href="<?php echo admin_url('admin.php').'?page=xs-mcs-currencies&action=edit&currency='.$currency['name'];?>"><?php esc_html_e('Edit', 'xs-mcs'); ?></a>
									<a class="button xs-mcs-delete-currency" href="<?php echo wp_nonce_url( admin_url('admin.php').'?page=xs-mcs-currencies&action=delete&currency='.$currency['id'] , 'delete_currency', 'delete_currency_nounce' );?>"><?php esc_html_e('Delete', 'xs-mcs'); ?></a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr>
							<th><?php esc_html_e('Currency', 'xs-mcs'); ?></th>
							<th><?php esc_html_e('Value', 'xs-mcs'); ?></th>
							<th><?php esc_html_e('Status', 'xs-mcs'); ?></th>
							<th><?php esc_html_e('Actions', 'xs-mcs'); ?></th>
						</tr>
					</tfoot>
				</table>
			</form>	
		</div>
	</div>
</div>