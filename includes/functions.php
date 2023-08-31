<?php
	function xs_mcs_currency_converer($base_curr, $target_curr,$value=1){
		$XSMCS_Options = new XSMCS_Options();
		if(!$base_curr){
			$base_curr = get_woocommerce_currency();
		}
		$api_key = $XSMCS_Options->options['api_key'];
		$data_pair = $base_curr.'_'.$target_curr;
		
		$data_url = 'http://free.currencyconverterapi.com/api/v6/convert?q='.$data_pair.'&compact=ultra&apiKey='.$api_key;
		$data = wp_remote_get($data_url);
		$data = wp_remote_retrieve_body( $data );
		$data = json_decode($data, true);
		if( isset($data['error']) ){
			return array(
				'status' 	=> false,
				'msg'		=> $data['error']
			);
		}
		if( isset($data[$data_pair]) ){
			return array( 
				'status' => true,
				'val' => $data[$data_pair]
			);
		}else{
			return array(
				'status' 	=> false,
				'msg'		=> esc_html__('Currency not supported', 'xs-mcs')
			);
		}
	}
	
	function xs_mcs_auto_update_currencies_values(){
		$XSMCS_Currency = new XSMCS_Currency();
		$currencies = $XSMCS_Currency->get_currencies('publish');
		
		foreach($currencies as $curr){
			if($curr['auto_update'] == 'yes'){
				$updated_val = xs_mcs_currency_converer( '', strtoupper($curr['name']) );
				
				if($updated_val['status']){
					$updated_val = $updated_val['val'];
					update_post_meta( $curr['id'], 'value', $updated_val );
					update_post_meta( $curr['id'], 'manualy_updated', 'no' );
					update_post_meta( $curr['id'], 'auto_updated', 'yes' );
					update_post_meta( $curr['id'], 'updated_on', time() );
				}
			}
		}
	}
	
	function xs_mcs_get_user_currency(){
		if( is_user_logged_in() ){
			$user_saved_currency = get_user_meta( get_current_user_id(), 'xs_mcs_switch_currency', true );
		}else{
			//get from cookies
			$user_saved_currency = isset($_COOKIE['xs_mcs_switch_currency']) ? $_COOKIE['xs_mcs_switch_currency'] : '';
		}
		if( !$user_saved_currency ){ // if currency is empty then get default currency
			return strtolower( get_woocommerce_currency() );
		}
		
		return $user_saved_currency ;
	}		
	
	function xs_mcs_currency_switcher_in_fragments($fragments){
		ob_start();
		xs_mcs_get_currency_switcher();
		$fragments['div.xs-mcs-curr-switcher-wrap'] = ob_get_clean();
		return $fragments;
	}
	
	
	/* Show currency switcher front side 	*/
	/* @params null							*/
	/* return null 							*/
	function xs_mcs_get_currency_switcher($switcher_currencies = false){
		$XSMCS_Options = new XSMCS_Options();
		if(  $XSMCS_Options->options['enable_switcher'] == 'yes' ){
			echo xs_mcs_get_currency_switcher_html();
		}
	}
	
	function xs_mcs_get_currency_switcher_html($switcher_currencies = false){
		$XSMCS_Options = new XSMCS_Options();
		if( !$switcher_currencies ){
			$switcher_currencies = array();
			$switcher_currencies[] = $XSMCS_Options->options['switcher_currencies'];
		}
		if(empty($switcher_currencies))
			return;
		
		$wc_currencies = get_woocommerce_currencies();
		$shoosen_curr = xs_mcs_get_user_currency();
		$switcher_currencies[] = strtolower( get_option( 'woocommerce_currency' ) );
		
		unset( $switcher_currencies[array_search($shoosen_curr, $switcher_currencies)] );

		ob_start();
		?>
		<div class="xs-mcs-curr-switcher-wrap <?php echo xs_mcs_get_user_currency();?>">
			<div class="xs-mcs-curr-switcher">
				<div class="xs-mcs-selected-curr" class="xs-mcs-shoosen"><?php echo strtoupper($shoosen_curr); ?><span class="xs-mcs-curr-name"><?php echo $wc_currencies[strtoupper($shoosen_curr)]; ?></span></div>
				<div class="xs-mcs-currencies">
					<?php foreach($switcher_currencies as $curr){ ?>
						<a data-curr="<?php echo $curr; ?>"><?php echo strtoupper($curr); ?><span class="xs-mcs-curr-name"><?php echo $wc_currencies[strtoupper($curr)]; ?></span></a>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
	function xs_mcs_switch_currency(){
		$curr = sanitize_text_field($_POST['currency']);
		$response = array();
		$response['currency'] = $curr;
		
		if( is_user_logged_in() ){
			update_user_meta(get_current_user_id(), 'xs_mcs_switch_currency', $curr);
			$response['success'] = true;
		}else{
			$response['success'] = false;
			$response['guest'] = 1;
		}
		wp_send_json( $response );
		die();
	}
	
	/* change currency in wc price filter 	*/
	/* @params Array							*/
	/* return Array						*/
	function xs_mcs_change_currency_wc_price_filter($args){
		
		$change_currency = true;
		global $wp;
		$request = explode( '/', $wp->request );

		if( ( end($request) == 'orders' && is_account_page() ) || is_admin() || is_wc_endpoint_url( 'order-received' ) || is_wc_endpoint_url( 'view-order' ) )
			$change_currency = false;
		if( !apply_filters('xs_mcs_change_currency', $change_currency) )
			return $args;
		
		// get user selected currency
		$XSMCS_Currency = new XSMCS_Currency();
		$currency = $XSMCS_Currency->get_currency( xs_mcs_get_user_currency() );		
		
		// if it is default currency or not a valid currency then return 
		if( !$currency['ID'] )
			return $args;
		
		$args['currency'] = strtoupper( $currency['name'] );
		
		return $args; 
	}
	
	/* load_translation_files 		*/
	/* @params float				*/
	/* return float					*/
	function xs_mcs_change_price_wc_price_filter($raw_price){
		$change_currency = true;
		global $wp;
		$request = explode( '/', $wp->request );

		if( ( end($request) == 'orders' && is_account_page() ) || is_admin() || is_wc_endpoint_url( 'order-received' ) || is_wc_endpoint_url( 'view-order' ) )
			$change_currency = false;
		
		if( !apply_filters('xs_mcs_change_currency', $change_currency) )
			return $raw_price;
		
		// get user selected currency
		$XSMCS_Currency = new XSMCS_Currency();
		$currency = $XSMCS_Currency->get_currency( xs_mcs_get_user_currency() );		
		
		// if it is default currency or not a valid currency then return 
		if( !$currency['ID'] )
			return $raw_price;
		
		$currency_value = $currency['value'];
		if($raw_price < 0){
			return ( $raw_price * $currency_value )* -1; 
		}else{
			return ( $raw_price * $currency_value );
		}
	}
	
	function xs_mcs_exchange_fee_in_cart($cart) {
		// get user selected currency
		$XSMCS_Currency = new XSMCS_Currency();
		$currency = $XSMCS_Currency->get_currency( xs_mcs_get_user_currency() );		
		
		// if it is default currency or not a valid currency then return 
		if( ( is_admin() ) || !$currency['ID'] ){
			WC()->session->set('xs_mcs_curr_data', false);
			return;
		}
		
		global $woocommerce;
		$exchange_fee = $currency['exchange_fee']['value'];
		$exchange_fee_type = $currency['exchange_fee']['type'];
		
		$cart_total = 0;
		if($cart->cart_contents_total){
			$cart_total += $cart->cart_contents_total;
		}
		
		if($cart->tax_total){
			$cart_total += $cart->tax_total;
		}
		
		if($cart->shipping_total){
			$cart_total += $cart->shipping_total;
		}
		
		if($cart->shipping_tax_total){
			$cart_total += $cart->shipping_tax_total;
		}
		
		if($cart->fee_total){
			$cart_total += $cart->fee_total;
		}
		
		if($cart->fee_tax){
			$cart_total += $cart->fee_tax;
		}
		
		$cart_total = $cart_total * $currency['value'];
		
		if( $currency['exchange_fee']['type'] == 'percentage' )
			$exchange_fee = $cart_total * ($exchange_fee / 100);
		
		$exchange_fee = ( 1/$currency['value'] ) * $exchange_fee;
		
		$xs_mcs_curr_data = array(
			'currency' 		=> $currency,
			'exchange_fee'	=> $exchange_fee
		);
		
		WC()->session->set('xs_mcs_curr_data', $xs_mcs_curr_data);
	}
	
	function xs_mcs_add_exchange_fee_in_cart_total($total, $cart_instance){
		$xs_mcs_curr_data = WC()->session->get('xs_mcs_curr_data');
		if( $xs_mcs_curr_data ){
			$exchange_fee = round($xs_mcs_curr_data['exchange_fee'], 2);
			$total = $total + $exchange_fee;
		}
		return $total;
	}
	
	function xs_mcs_display_exchange_fee_cart_totals(){
		$xs_mcs_curr_data = WC()->session->get('xs_mcs_curr_data');
		if( $xs_mcs_curr_data and $xs_mcs_curr_data['exchange_fee'] > 0 ){
			$exchange_fee = $xs_mcs_curr_data['exchange_fee'];
			?>
			<tr class="xsmcs-exchange-fee-total">
				<th><?php esc_html_e( 'Currency Exchange Fee', 'xs-mcs' ); ?></th>
				<td data-title="<?php esc_attr_e( 'Currency Exchange Fee', 'xs-mcs' ); ?>"><?php echo wc_price($exchange_fee); ?></td>
			</tr>
			<?php
		}
	}
	
	
	function xs_mcs_exchange_fee_in_order($order, $data){
		$xs_mcs_curr_data = WC()->session->get('xs_mcs_curr_data');
		if( $xs_mcs_curr_data ){
			$xs_mcs_curr_data['order_total_inc_exchange_fee'] = $order->get_total();
			$xs_mcs_curr_data['order_total_exc_exchange_fee'] = round($order->get_total()-$xs_mcs_curr_data['exchange_fee'], 2);
		}
		
		if( $xs_mcs_curr_data  and $xs_mcs_curr_data['exchange_fee'] > 0 ){
			$exchange_fee = $xs_mcs_curr_data['exchange_fee'];
		
			$item = new WC_Order_Item_Fee();
			$fee = new stdClass;
			
			$fee->id = 'xsmcs-currency-exchange-fee';
			$fee->name = esc_html__( 'Currency Exchange Fee', 'xs-mcs' );
			$fee->tax_class = '';
			$fee->taxable = 0;
			$fee->amount = $exchange_fee;
			$fee->total = $exchange_fee;
			$fee->tax_data = array();
			$fee->tax = 0;
			
			$item->legacy_fee = $fee; // @deprecated For legacy actions.
			$item->legacy_fee_key = $fee_key; // @deprecated For legacy actions.
			$item->set_props( 
				array(
					'name'      => $fee->name,
					'tax_class' => $fee->taxable ? $fee->tax_class: 0,
					'amount'    => $fee->amount,
					'total'     => $fee->total,
					'total_tax' => $fee->tax,
					'taxes'     => array(
						'total' => $fee->tax_data,
					),
				)
			);
			$order->add_item( $item );
			$order_id = $order->save();
		}
		$order_id = $order->save();
		update_post_meta($order_id ,'xs_mcs_curr_data', $xs_mcs_curr_data);
		xs_mcs_save_order_currency( $order_id, $xs_mcs_curr_data );
	}
	
	function xs_mcs_save_order_currency( $order_id, $xs_mcs_curr_data ){
		global $wpdb;
		$order = wc_get_order( $order_id );
		if(!$xs_mcs_curr_data){
			$xs_mcs_curr_data = array();
			$xs_mcs_curr_data['currency']['name'] = strtolower(get_woocommerce_currency());
			$xs_mcs_curr_data['currency']['value'] = 1;
			$xs_mcs_curr_data['order_total_exc_exchange_fee'] = $order->get_total();
			$xs_mcs_curr_data['order_total_inc_exchange_fee'] = $order->get_total();
			$xs_mcs_curr_data['exchange_fee'] = 0;
		}
		$order_currencies_table = $wpdb->prefix . "xsmcs_order_currencies";
		if( $wpdb->get_var("SELECT `order_id` FROM {$order_currencies_table} WHERE `order_id`={$order_id}") == $order_id){
			// update
			$wpdb->update(
				$order_currencies_table,
				array(
					'order_currency' 					=> esc_html($xs_mcs_curr_data['currency']['name']),
					'currency_value'					=> esc_html($xs_mcs_curr_data['currency']['value']), 
					'order_total_exc_fee' 				=> esc_html($xs_mcs_curr_data['order_total_exc_exchange_fee']),
					'order_total_inc_fee' 				=> esc_html($xs_mcs_curr_data['order_total_inc_exchange_fee']),
					'exchange_fee' 						=> esc_html($xs_mcs_curr_data['exchange_fee']),
					'order_total_1_exc_fee' 			=> esc_html($xs_mcs_curr_data['order_total_exc_exchange_fee'])*esc_html($xs_mcs_curr_data['currency']['value']),
					'order_total_1_inc_fee' 			=> esc_html($xs_mcs_curr_data['order_total_inc_exchange_fee'])*esc_html($xs_mcs_curr_data['currency']['value']),
					'exchange_fee_1' 					=> esc_html($xs_mcs_curr_data['exchange_fee']*$xs_mcs_curr_data['currency']['value']),
				),
				array(
					'order_id' 							=> $order_id,
				)
			);
		}else{
			//Insert
			$wpdb->insert($order_currencies_table, array(
				'order_id' 							=> $order_id,
				'order_currency' 					=> esc_html($xs_mcs_curr_data['currency']['name']),
				'currency_value'					=> esc_html($xs_mcs_curr_data['currency']['value']), 
				'order_total_exc_fee' 				=> esc_html($xs_mcs_curr_data['order_total_exc_exchange_fee']),
				'order_total_inc_fee' 				=> esc_html($xs_mcs_curr_data['order_total_inc_exchange_fee']),
				'exchange_fee' 						=> esc_html($xs_mcs_curr_data['exchange_fee']),
				'order_total_1_exc_fee' 			=> esc_html($xs_mcs_curr_data['order_total_exc_exchange_fee'])* esc_html($xs_mcs_curr_data['currency']['value']),
				'order_total_1_inc_fee' 			=> esc_html($xs_mcs_curr_data['order_total_inc_exchange_fee']) * esc_html($xs_mcs_curr_data['currency']['value']),
				'exchange_fee_1' 					=> esc_html($xs_mcs_curr_data['exchange_fee']) * esc_html($xs_mcs_curr_data['currency']['value']),
			));
		}
	}
	
	function xs_mcs_order_currency($order, $data){
		add_filter( 'woocommerce_currency', 'xs_mcs_woocommerce_currency', 99, 1);
		$xs_mcs_curr_data = WC()->session->get('xs_mcs_curr_data');
		if( get_woocommerce_currency() !== strtoupper($xs_mcs_curr_data['currency']['name']) ){
			$order->set_currency( strtoupper( $xs_mcs_curr_data['currency']['name']) );
		}
		$order->save();
	}
	
	function xs_mcs_woocommerce_currency($cur){
		// get user selected currency
		$XSMCS_Currency = new XSMCS_Currency();
		$currency = $XSMCS_Currency->get_currency( xs_mcs_get_user_currency() );		
		
		// if it is default currency or not a valid currency then return 
		if( !$currency['ID'] )
			return $curr;
		
		return strtoupper( xs_mcs_get_user_currency() );
	}
?>