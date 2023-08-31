<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;   //Exit if accessed directly.
}

if(!class_exists('XSMCS_Admin_Menu')){
	class XSMCS_Admin_Menu{
		
		private $action;
		private $currency_id;
		public $XSMCS_Currency;
		public $xsmcs;
		/*	Constructor	*/
		public function __construct($xsmcs){
			$this->xsmcs = $xsmcs;
			$this->action = isset( $_GET['action'] ) ? sanitize_text_field($_GET['action']) : '';
			$this->currency_id = isset( $_GET['currency'] ) ? sanitize_text_field($_GET['currency']) : '';
			$this->XSMCS_Currency = $xsmcs->XSMCS_Currency;
			$this->options = $xsmcs->XSMCS_Options->options;
			
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts') );
			// Admin Menu
			add_action( 'admin_menu', array( $this, 'admin_menu') );
			add_action( 'add_meta_boxes', array( $this, 'order_currency_info_meta_box') );
			
			
			add_action( 'wp_ajax_xs_mcs_get_currency_value', array($this, 'get_currency_value') );
			add_action( 'wp_ajax_nopriv_xs_mcs_get_currency_value', array($this, 'get_currency_value') );
			
			add_action( 'wp_ajax_xs_mcs_get_currency_value_inc_exc_fee', array($this, 'get_currency_value_inc_exc_fee') );
			add_action( 'wp_ajax_nopriv_xs_mcs_get_currency_value_inc_exc_fee', array($this, 'get_currency_value_inc_exc_fee') );
			
			add_action( 'wp_ajax_xs_mcs_save_options', array($this->xsmcs->XSMCS_Options, 'save_options') );
			add_action( 'wp_ajax_nopriv_xs_mcs_save_options', array($this->xsmcs->XSMCS_Options, 'save_options') );
			add_action( 'wp_ajax_xs_mcs_send_mail' ,array($this,'xs_mcs_send_mail'));
			
		}
		
		/*	enqueue necessary assets		*/
		/*	@params	null					*/
		/*	returns	null					*/
		public function admin_scripts(){
			wp_enqueue_script( 'select2' );
			wp_register_script( 'xs-mcs-script', XSMCS_ROOT_URL.'/includes/admin/assets/js/xs-mcs-admin-script.js', array('jquery', 'select2') );
			wp_localize_script( 'xs-mcs-script' , 'xsmcs', array( 'ajax_url' => admin_url('admin-ajax.php') ) );
			
			wp_enqueue_script( 'jscolor', XSMCS_ROOT_URL.'/includes/admin/assets/js/jscolor.js', array('jquery') );
			wp_enqueue_script( 'xs-mcs-script' );
			
			wp_enqueue_style( 'xs-mcs-css', XSMCS_ROOT_URL.'/includes/admin/assets/css/xs-mcs-admin-css.css' );
			wp_enqueue_style( 'select2', WC()->plugin_url() . '/assets/css/select2.css' );
		}
		
		/*	adding admin Menu	*/
		/*	@params	null		*/
		/*	returns	null		*/
		public function admin_menu(){
			add_menu_page( esc_html__( 'Currencies', 'xs-mcs' ), esc_html__('Multi Currency', 'xs-mcs'), 'manage_options', 'xs-mcs-currencies', array($this, 'currencies_page') );
			add_submenu_page ( 'xs-mcs-currencies', esc_html__( 'Settings', 'xs-mcs'), esc_html__( 'Settings', 'xs-mcs' ), 'manage_options', 'xs-mcs-options', array($this, 'options_page') );
			add_submenu_page ( 'xs-mcs-currencies', esc_html__( 'Support', 'xs-mcs'), esc_html__( 'Support', 'xs-mcs' ), 'manage_options', 'xs-mcs-support', array($this, 'support_page') );
		}
		
		public function currencies_page(){
			if( isset($_POST['add_new_currency_nounce']) and wp_verify_nonce($_POST['add_new_currency_nounce'], 'add_new_currency') ){
				$response = $this->XSMCS_Currency->save_currency(array_map('sanitize_text_field', $_POST['currency']));
				if(!$response['success']){
					$this->action = 'new';
				}
			}elseif( isset($_POST['edit_currency_nounce']) and wp_verify_nonce($_POST['edit_currency_nounce'], 'edit_currency') ){
				$response = $this->XSMCS_Currency->save_currency(array_map('sanitize_text_field', $_POST['currency']),sanitize_text_field($_POST['previous_currency']));
				if(!$response['success']){
					$this->action = 'edit';
					$this->currency_id = sanitize_text_field($_POST['previous_currency']);
				}
			}else if( isset($_POST['save_currency']) && (!wp_verify_nonce($_POST['add_new_currency_nounce'], 'add_new_currency') ||  !wp_verify_nonce($_POST['edit_currency_nounce'], 'edit_currency')) ){
				$response = array(
					'success' => false,
					'message' => esc_html__('Nounce not varified.', 'xs-mcs')
				);
			}
			
			$wc_currencies = get_woocommerce_currencies();
			$countries_obj   = new WC_Countries();
			$wc_countries   = $countries_obj->__get('countries');
			$wc_payment_gateways = WC()->payment_gateways->get_available_payment_gateways();
			switch( $this->action ){
				case 'new':
					$currency = $this->XSMCS_Currency->get_currency();
					if( isset( $response['success'] ) and !$response['success']){
						$currency = array_map('sanitize_text_field', $_POST['currency']);
					}
					include XSMCS_ROOT_PATH.'/includes/admin/views/view.currency-form.php';
					break;
				case 'edit':
					$currency = $this->XSMCS_Currency->get_currency( $this->currency_id );
					if($currency){
						include XSMCS_ROOT_PATH.'/includes/admin/views/view.currency-form.php';	
						break;
					}else{
						$response = array(
							'success' => false,
							'message' => esc_html__('Currency not found', 'xs-mcs'),
						);
					}
				default:
					if( $this->action == 'delete' and isset($_GET['delete_currency_nounce']) and wp_verify_nonce($_GET['delete_currency_nounce'], 'delete_currency') ){
						$response = $this->XSMCS_Currency->delete_currency( $this->currency_id );
					}elseif( $this->action == 'delete' and !wp_verify_nonce($_GET['delete_currency_nounce'], 'delete_currency') ){
						$response = array(
							'success' => false,
							'message' => esc_html__('Nounce not varified.', 'xs-mcs'),
						);
					}
					$get_currencies = $this->XSMCS_Currency->get_currencies();
					include XSMCS_ROOT_PATH.'/includes/admin/views/view.currencies.php';
					break;
			}
		}
		
		/*	Loading admin settings page	*/
		/*	@params	null			*/
		/*	returns	null			*/
		public function options_page(){
			include XSMCS_ROOT_PATH.'/includes/admin/views/view.options-page.php';
		}
		/*	Loading admin settings page	*/
		/*	@params	null			*/
		/*	returns	null			*/
		public function support_page(){
			include XSMCS_ROOT_PATH.'/includes/admin/views/view.support-page.php';
		}
		
		
		/*	Loading Reports page	*/
		/*	@params	null			*/
		/*	returns	null			*/
		
		public function reports_page(){
			global $wpdb;
			$order_currencies_table = $wpdb->prefix . "xsmcs_order_currencies";
			$orders_table = $wpdb->prefix . "posts";
			
			$top_currencies = $wpdb->get_results("SELECT oc.order_currency, COUNT(oc.order_currency) as currency_count, SUM(oc.order_total_inc_fee) as total_currency_inc_exc_fee, SUM(oc.order_total_exc_fee) as total_currency_exc_fee, SUM(oc.exchange_fee) as exchange_fee, SUM(oc.order_total_1_inc_fee) as total_currency_1_inc_fee, SUM(oc.order_total_1_exc_fee) as total_currency_1_exc_fee, SUM(oc.exchange_fee_1) as exchange_fee_1  FROM {$order_currencies_table} as oc INNER JOIN {$orders_table} as ot ON oc.order_id=ot.ID WHERE ot.post_status NOT IN ('trash', 'wc-cancelled') GROUP BY oc.order_currency ORDER BY total_currency_inc_exc_fee DESC LIMIT 5");
			$sum_all_orders = $wpdb->get_var("SELECT SUM(pm.meta_value) FROM {$wpdb->prefix}postmeta as pm INNER JOIN {$wpdb->prefix}posts as p ON pm.post_id = p.ID WHERE p.post_status NOT IN ('trash', 'wc-cancelled') AND p.post_type LIKE 'shop_order' AND pm.meta_key LIKE '_order_total'");
			$top_currencies_percentage = array();
			
			foreach($top_currencies as $cur){
				if($cur->order_currency == strtolower(get_woocommerce_currency()))
					continue;
				$sum_curr = $wpdb->get_var("SELECT SUM(oc.order_total_inc_fee) FROM {$order_currencies_table} as oc INNER JOIN {$orders_table} as ot ON oc.order_id=ot.ID WHERE ot.post_status NOT IN ('trash', 'wc-cancelled') and oc.order_currency = '{$cur->order_currency}'");
				$top_currencies_percentage[$cur->order_currency] = ($sum_curr/$sum_all_orders)*100;
			}
			$top_currencies_percentage_sum = 0;
			foreach($top_currencies_percentage as $percentage){
				$top_currencies_percentage_sum += round($percentage, 2);
			}
			$top_currencies_percentage[strtolower(get_woocommerce_currency())] = 100 - $top_currencies_percentage_sum;
			arsort($top_currencies_percentage);
			$wc_currencies = get_woocommerce_currencies();
			include XSMCS_ROOT_PATH.'/includes/admin/views/view.reports-page.php';
		}
		/* Ajax callback for currency value */
		/* @params null 					*/
		/* return float currecny value		*/
		
		public function get_currency_value(){
			$target_curr = sanitize_text_field($_POST['currency_name']);
			$val = xs_mcs_currency_converer('', $target_curr );
			
			if($val['status']!== false){
				wp_send_json(
					array(
						'success' => true,
						'value'   => $val['val'], 2
					)
				);
			}else{
				wp_send_json(
					array(
						'success' 	=> false,
						'value'   	=> 0,
						'msg'		=> $val['msg'],
					)
				);
			}
			die();
		}
		
		/* Ajax callback for currency value including exchange fee 	*/
		/* @params null 											*/
		/* return float currecny value								*/
		
		public function get_currency_value_inc_exc_fee(){
			$target_curr = sanitize_text_field($_POST['currency_name']);
			$value = sanitize_text_field($_POST['value']);
			if(!$value){
				$target_currency = $this->XSMCS_Currency->get_currency($target_curr);
				if(!$target_currency['ID']){
					$value = xs_mcs_currency_converer('', $target_curr );
					if($value['status']){
						$value = $value['val'];
					}else{
						$value = 0;
					}
				}else{
					$value = $target_currency['value'];
				}
			}
			
			
			if( $_POST['exchange_fee_type'] == 'percentage' ){
				$calculated_value = $value + ($value*(sanitize_text_field($_POST['exchange_fee'])/100));
			}else{
				$calculated_value = $value + sanitize_text_field($_POST['exchange_fee']);
			}
			$calculated_value = wc_price( $calculated_value, array( 'currency' => sanitize_text_field($_POST['currency_name']) ) );
			echo '<span class="xs-mcs-base-price">'.wc_price(1).'</span> '.esc_html__('will be equal to ', 'xs-mcs' ).'<span class="xs-mcs-calculated-price">'.$calculated_value.'</span>';
			die();
		}
		
		public function order_currency_info_meta_box() {
			add_meta_box( 'xs-mcs-order-currency', esc_html__( 'Order Currency', 'xs-mcs' ), array($this, 'order_currency_info'), 'shop_order', 'side' );
		}
		 
		/**
		 * Meta box display callback.
		 *
		 * @param WP_Post $post Current post object.
		 */
		function order_currency_info( $post ) {
			$currency_info = get_post_meta($post->ID, 'xs_mcs_curr_data', true);
			if( $currency_info ){
				?>
				<ul class="xs-mcs-order-curr-info">
					<li class="wide">
						<label>Currency: <?php echo $currency_info['currency']['title']; ?></label>
					</li>
					
					<li class="wide">
						<label>Currency Value: <?php echo wc_price($currency_info['currency']['value'], array('currency' => strtoupper($currency_info['currency']['name']))); ?></label>
					</li>
					
					<li class="wide">
						<label>Exchange Fee type: <?php if( $currency_info['currency']['exchange_fee']['type'] == 'flat' ){
							echo wc_price($currency_info['currency']['exchange_fee']['value'], array('currency' => strtoupper($currency_info['currency']['name'])));
						}else{
							echo $currency_info['currency']['exchange_fee']['value'].'%'; 
						} ?></label>
					</li>
					
					<li class="wide">
						<label>Exchange Fee: <?php echo wc_price( ($currency_info['currency']['value']*$currency_info['exchange_fee']), array('currency' => strtoupper($currency_info['currency']['name']))); ?></label>
					</li>
					
					<li class="wide">
						<label>Order Total(EXC Exchange Fee): <?php echo wc_price( ($currency_info['currency']['value']*$currency_info['order_total_exc_exchange_fee']), array('currency' => strtoupper($currency_info['currency']['name']))); ?></label>
					</li>
					
					<li class="wide">
						<label>Order Total(INC Exchange Fee): <?php echo wc_price( ($currency_info['currency']['value']*$currency_info['order_total_inc_exchange_fee']), array('currency' => strtoupper($currency_info['currency']['name']))); ?></label>
					</li>
				</ul>
				<?php
			}else{
				echo apply_filters( 'xs_mcs_default_order_currency_note', esc_html__('Order is placed in default currency', 'xs-mcs'), $post );
			}
		}
		public function xs_mcs_send_mail(){
			$data = array();
	        parse_str($_POST['data'], $data);
	        $data['plugin_name'] = 'Multi Currency Switcher';
	        $data['version'] = 'lite';
	        $data['website'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$_SERVER['HTTP_HOST'];
	        $to = 'xfinitysoft@gmail.com';
	        switch ($data['type']) {
	        	case 'report':
	        		$subject = 'Report a bug';
	        		break;
	        	case 'hire':
	        		$subject = 'Hire us to customize/develope Plugin/Theme or WordPress projects';
	        		break;
	        	
	        	default:
	        		$subject = 'Request a Feature';
	        		break;
	        }
			
			$body = '<html><body><table>';
			$body .='<tbody>';
			$body .='<tr><th>User Name</th><td>'.$data['xs_mcs_name'].'</td></tr>';
			$body .='<tr><th>User email</th><td>'.$data['xs_mcs_email'].'</td></tr>';
			$body .='<tr><th>Plugin Name</th><td>'.$data['plugin_name'].'</td></tr>';
			$body .='<tr><th>Version</th><td>'.$data['version'].'</td></tr>';
			$body .='<tr><th>Website</th><td><a href="'.$data['website'].'">'.$data['website'].'</a></td></tr>';
			$body .='<tr><th>Message</th><td>'.$data['xs_mcs_message'].'</td></tr>';
			$body .='</tbody>';
			$body .='</table></body></html>';
			$headers = array('Content-Type: text/html; charset=UTF-8');
			$params ="name=".$data['xs_mcs_name'];
			$params.="&email=".$data['xs_mcs_email'];
			$params.="&site=".$data['website'];
			$params.="&version=".$data['version'];
			$params.="&plugin_name=".$data['plugin_name'];
			$params.="&type=".$data['type'];
			$params.="&message=".$data['xs_mcs_message'];
			$sever_response = wp_remote_get("https://xfinitysoft.com/wp-json/plugin/v1/quote/save/?".$params);
	        $se_api_response = json_decode( wp_remote_retrieve_body( $sever_response ), true );
			if($se_api_response['status']){
				$mail = wp_mail( $to, $subject, $body, $headers );
				wp_send_json(array('status'=>true));
			}else{
				wp_send_json(array('status'=>false));
			}
			wp_die();
		}
	}
	$XSMCS_Admin_Menu = new XSMCS_Admin_Menu($this);
}
?>