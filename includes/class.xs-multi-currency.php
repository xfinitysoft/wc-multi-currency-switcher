<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;   //Exit if accessed directly.
}

if(!class_exists('XSMultiCurrency')){
	class XSMultiCurrency{
		public $version;
		public $XSMCS_Currency;
		public $options;
		public $XSMCS_Options;
		
		/**
		* Constructor.
		*/
		public function __construct(){
			$this->version = '0.0.1';
			if( isset($_GET['currency']) and $_GET['currency']!=='' and !is_admin() ){
				$this->xs_mcs_clean_post_headers_before_reload();
			}
			
			add_action( 'init', array( $this, 'load_translation_files') );
			add_action( 'plugins_loaded', array( $this, 'includes') );
			add_action( 'init', array($this, 'xsmcs_init') );
			add_filter( 'cron_schedules', array($this, 'custom_cron_interval') );
			add_action( 'xs_mcs_auto_update_currencies_values', 'xs_mcs_auto_update_currencies_values' );
			
			add_action( 'wp_footer', 'xs_mcs_get_currency_switcher' );
			add_action( 'wp_enqueue_scripts' , array($this,'xs_mcs_styles_scripts'));
			add_action( 'wp_footer', array( $this, 'enqueue_scripts' ) );
			add_action( 'widgets_init', array($this, 'load_widget') );
			
			add_action( 'wp_ajax_xs_mcs_switch_currency', 'xs_mcs_switch_currency' );
			add_action( 'wp_ajax_nopriv_xs_mcs_switch_currency', 'xs_mcs_switch_currency' );
			
			add_filter( 'wc_price_args', 'xs_mcs_change_currency_wc_price_filter' );
			add_filter( 'raw_woocommerce_price', 'xs_mcs_change_price_wc_price_filter' );
			
			add_filter( 'woocommerce_add_to_cart_fragments', 'xs_mcs_currency_switcher_in_fragments');
			
			add_action( 'woocommerce_calculate_totals', 'xs_mcs_exchange_fee_in_cart', 999, 1 );
			add_filter( 'woocommerce_calculated_total', 'xs_mcs_add_exchange_fee_in_cart_total', 99, 2 );
			
			add_action( 'woocommerce_cart_totals_before_order_total', 'xs_mcs_display_exchange_fee_cart_totals', 999, 1 );
			add_action( 'woocommerce_review_order_before_order_total', 'xs_mcs_display_exchange_fee_cart_totals', 999, 1);
			add_action( 'woocommerce_checkout_create_order', 'xs_mcs_exchange_fee_in_order', 99, 2 );
			add_action( 'woocommerce_checkout_create_order', 'xs_mcs_order_currency', 99, 3 );
		}
		
		/* load_translation_files 	*/
		/* @params null				*/
		/* return null				*/
		public function load_translation_files() {
			load_plugin_textdomain('xs-mcs', false, basename( dirname( __FILE__ ) ) . '/languages');	
		}
		
		public function includes(){
			include XSMCS_ROOT_PATH.'/includes/class.xsmcs-options.php';
			include XSMCS_ROOT_PATH.'/includes/functions.php';
			include XSMCS_ROOT_PATH.'/includes/class.xsmcs-currency.php';
			include XSMCS_ROOT_PATH.'/includes/class.xsmcs-widget.php';
		}
		/* xs_mcs_init			 	*/
		/* @params null				*/
		/* return null				*/
		public function xsmcs_init(){
			$this->XSMCS_Currency = new XSMCS_Currency();
			$this->XSMCS_Currency->register_post_type();
			$this->XSMCS_Options = new XSMCS_Options();
			$this->options = $this->XSMCS_Options->options;
			$this->register_auto_update_cron_event();
			if( is_admin() ){
				$this->init_admin();
			}
		}
		
		/* initialize admin		 	*/
		/* @params null				*/
		/* return null				*/
		private function init_admin(){
			include XSMCS_ROOT_PATH.'/includes/admin/class.xsmcs-admin-menu.php';
		}
		
		/* Registering cron event for updating currencies 	*/
		/* @params null										*/
		/* return null										*/
		public function register_auto_update_cron_event(){
			if( isset($this->options['auto_update_interval']) && $this->options['auto_update_interval'] != $this->options['xs_mcs_prev_auto_update_interval'] ){
				wp_clear_scheduled_hook('xs_mcs_auto_update_currencies_values');
			}
			if (! wp_next_scheduled ( 'xs_mcs_auto_update_currencies_values' )) {
				if(isset($this->options['auto_update_interval'])){
					$scheduele = 'xs_mcs_every_'.$this->options['auto_update_interval'].'_sec';
					wp_schedule_event( time(), $scheduele, 'xs_mcs_auto_update_currencies_values' );
				}
				
			}
		}
		
		/* Adding custom interval for cron event 	*/
		/* @params Array of all schedueles			*/
		/* return Array of all schedueles			*/
		public function custom_cron_interval( $schedules ) {
			if(isset($this->options['auto_update_interval']) && $this->options['auto_update_interval'] != $this->options['xs_mcs_prev_auto_update_interval'] ){
				$prev_scheduele = 'xs_mcs_every_'.$this->options['xs_mcs_prev_auto_update_interval'].'_sec';
				unset($schedules[$prev_scheduele]);
			}
			if(isset($this->options['auto_update_interval'])){
				$scheduele_id = 'xs_mcs_every_'.$this->options['auto_update_interval'].'_sec';
				$display = esc_html__('Every', 'xs-mcs').' '.$this->options['auto_update_interval'].' '.esc_html__('Seconds', 'xs-mcs');
				$schedules[$scheduele_id] = array(
					'interval'	=> $this->options['auto_update_interval'],
					'display'	=> $display
				);	
			}
			
			return $schedules;
		}
		
		/* Register and load the widget	*/
		/* @params null					*/
		/* return null					*/
		public function load_widget() {
			register_widget( 'xs_mcs_widget' );
		}

		/* Register and load the style & script	*/
		/* @params null					*/
		/* return null					*/
		public function xs_mcs_styles_scripts(){
			wp_enqueue_style('xs-mcs-style',plugins_url('multi-currency-switcher/assets/css/xs-mcs-style.css'));
			wp_enqueue_script( 'xs-mcs-script', plugins_url('multi-currency-switcher/assets/js/xs-mcs-script.js'), array(),'',true);
    		wp_localize_script( 'xs-mcs-script', 'xs_mcs_object',
            			array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		}
		
		public function enqueue_scripts(){
			$inline_style = '.xs-mcs-curr-switcher{
					background:#'.$this->options["switcher_background"].';
					color:#'.$this->options["switcher_font_color"].';
				}
				.xs-mcs-selected-curr:hover{
					background:#'.$this->options["switcher_hover_background"].';
					color:#'. $this->options["switcher_font_color_hover"].';
				}
				.xs-mcs-curr-switcher a{
					color:#'. $this->options["switcher_font_color"].';
				}
				.xs-mcs-curr-switcher a:hover{
					color:#'. $this->options["switcher_font_color_hover"].';
					background:#'. $this->options["switcher_hover_background"].';
				}
				.xs-mcs-moving-dot{
					background-color:#'. $this->options["switcher_font_color"].';
				}';
			wp_register_style( 'xs-mcs-inline-style', false );
    		wp_enqueue_style( 'xs-mcs-inline-style' );
			wp_add_inline_style( 'xs-mcs-inline-style', $inline_style );
			
		}
		public function xs_mcs_clean_post_headers_before_reload(){
			$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$parsed_url = parse_url($current_url);
			$query = $parsed_url['query'];
			$query_array = array();
			parse_str($parsed_url['query'], $query_array);
			unset( $query_array['currency'] );
			$query = http_build_query($query_array);
			if($query){
				$current_url = $parsed_url['scheme'].'://'.$parsed_url['host'].$parsed_url['path'].'?'.$query;
			}else{
				$current_url = $parsed_url['scheme'].'://'.$parsed_url['host'].$parsed_url['path'];
			}
			
			header("Location: ".$current_url);
			die();
		}
	}
}
?>