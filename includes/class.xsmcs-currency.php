<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;   //Exit if accessed directly.
}

if(!class_exists('XSMCS_Currency')){
	class XSMCS_Currency{
		/*	Constructor	*/
		public function __construct(){
			
		}
		
		/*	register post type for currency	*/
		/*	@params	null					*/
		/*	returns	null					*/
		public function register_post_type(){
			$labels = array();

			$args = array(
				'labels'             => $labels,
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => false,
				'show_in_menu'       => false,
				'query_var'          => false,
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( )
			);

			register_post_type( 'xs-mcs-currency', $args );
		}
		
		/*	get currency by id				*/
		/*	@params	int id					*/
		/*	returns	_A_Array | Bool			*/
		public function get_currency( $id = false ){
			if($id){
				$curr = get_page_by_path( $id, OBJECT, 'xs-mcs-currency' );
			}else{
				$curr = false;
			}
			$currency = array(
				'ID'						=> $curr ? $curr->ID : false,
				'name' 						=> $curr ? $curr->post_name : '',
				'title'						=> $curr ? $curr->post_title : '',
				'value'						=> $curr ? get_post_meta( $curr->ID, 'value', true ) : '',
				'exchange_fee'				=> $curr ? get_post_meta( $curr->ID, 'exchange_fee', true ) : array( 'type'	=>  'percentage', 'value' => 0 ),
				'enable'					=> $curr ? $curr->post_status : 'draft',
			);
			if($curr){
				$currency['updated_on'] = get_post_meta( $curr->ID, 'updated_on', true );
				$currency['auto_update'] = get_post_meta( $curr->ID, 'auto_update', true );
				$currency['manualy_updated'] = get_post_meta( $curr->ID, 'manualy_updated', true );
			}
			return apply_filters( 'xs_mcs_get_currency', $currency, $curr );
		}
		
		/*	get add new currency			*/
		/*	@params	_A_Array					*/
		/*	returns	int id					*/
		public function save_currency( $raw_posted_currency, $currency_id = false ){
			$all_currencies = get_woocommerce_currencies();
			
			if( $currency_id ){
				$curr_obj = get_page_by_path( $currency_id, OBJECT, 'xs-mcs-currency' );
				if(!$curr_obj){
					// Editing a currency which dont exists
					return 	array( 
								'success' => false,
								'message'=> $all_currencies[strtoupper($currency_id)].' '.esc_html__('not found', 'xs-mcs')
							);
				}elseif($raw_posted_currency['name'] != strtoupper($currency_id)){
					$_curr_obj = get_page_by_path( $raw_posted_currency['name'], OBJECT, 'xs-mcs-currency' );
					if( $_curr_obj ){
						// Adding a currency which already exists
						return 	array( 
									'success' => false,
									'message' => $all_currencies[$raw_posted_currency['name']].' '.esc_html__('already exists', 'xs-mcs')
								);
					}
				}
			}else{
				$curr_obj = get_page_by_path( $raw_posted_currency['name'], OBJECT, 'xs-mcs-currency' );
				if( $curr_obj ){
					// Adding a currency which already exists
					return 	array( 
								'success' => false,
								'message' => $all_currencies[$raw_posted_currency['name']].' '.esc_html__('already exists', 'xs-mcs')
							);
				}
			}
			
			$auto_update = 'yes';
			// Validate posted data
			if( !isset($raw_posted_currency['name']) || empty($raw_posted_currency['name']) ){
				return 	array(
							'success' => false,
							'message' => esc_html__('Currency is not selected', 'xs-mcs')
						);
			}
			
			$args = array(
				'post_status' 	=> isset($raw_posted_currency['enable']) ? $raw_posted_currency['enable'] : 'draft',
				'post_type' 	=> 'xs-mcs-currency',
				'post_title' 	=> $all_currencies[$raw_posted_currency['name']],
				'post_name'		=> $raw_posted_currency['name'],
			);
			if($currency_id)
				$args['ID'] = $curr_obj->ID;
			
			$curr_id = wp_insert_post( $args );
			
			if($curr_id){
				// currency value
				$curr_value = isset( $raw_posted_currency['value'] ) ? $raw_posted_currency['value'] : '';
				if(isset($raw_posted_currency['value']) and !$raw_posted_currency['value']){
					// get currency value through api
					$curr_value = xs_mcs_currency_converer('', sanitize_text_field($raw_posted_currency['name']) );
					if($curr_value['status']){
						$curr_value = $curr_value['val'];
					}else{
						$curr_value = 0;
					}
					update_post_meta( $curr_id, 'auto_updated', 'yes' );
					update_post_meta( $curr_id, 'manualy_updated', 'no' );
					$updated_on = time();
				}else{
					$prev_value = false;
					if($currency_id)
						$prev_value = get_post_meta($curr_id, 'value', true);
						
					if( ($prev_value && $prev_value != $curr_value) || !$currency_id ){
						update_post_meta( $curr_id, 'manualy_updated', 'yes' );
						update_post_meta( $curr_id, 'auto_updated', 'no' );
						$updated_on = time();
					}else{
						$updated_on = false;
					}
				}
				update_post_meta( $curr_id, 'value', $curr_value );
				update_post_meta( $curr_id, 'exchange_fee', $raw_posted_currency['exchange_fee'] );
				update_post_meta( $curr_id, 'auto_update', $auto_update );
				if($updated_on)
					update_post_meta( $curr_id, 'updated_on', $updated_on );
				
				if($currency_id){
					$message = $all_currencies[$raw_posted_currency['name']].' '.esc_html__('Updated', 'xs-mcs');
				}else{
					$message = $all_currencies[$raw_posted_currency['name']].' '.esc_html__('Added', 'xs-mcs');
				}
				
				return 	array( 
							'success' => true,
							'message' => $message
						);
			
			}else{
				if($currency_id){
					$message = $all_currencies[$raw_posted_currency['name']].' '.esc_html__('not Updated', 'xs-mcs');
				}else{
					$message = $all_currencies[$raw_posted_currency['name']].' '.esc_html__('not Added', 'xs-mcs');
				}
				return 	array( 
							'success' => false, 
							'message' => $message
						);
			}
		}
		
		/*	Get all added currencies							*/
		/*	@params	string, enabled, disabled or empty for both	*/
		/*	@params	bool, key_value_pair or ''						*/
		/*	returns	Array										*/
		public function get_currencies($status = '', $key_value_pair=false){
			$args = array(
				'post_type' 		=> 'xs-mcs-currency',
				'post_status'		=> array('draft', 'publish'),
				'numberposts'		=> 2
			);
			switch($status){
				case 'enabled':
					$args['post_status'] = 'publish';
					break;
				case 'disabled':
					$args['post_status'] = 'draft';
					break;
				default:
					$args['post_status'] = array('draft', 'publish');
			}
			$curr_objects = get_posts($args);
			$currencies = array();
			foreach($curr_objects as $obj){
				if($key_value_pair){
					$currencies[$obj->post_name] = $obj->post_title;
				}else{
					$currencies[] = array(
						'id'						=> $obj->ID,
						'name' 						=> $obj->post_name,
						'value'						=> get_post_meta( $obj->ID, 'value', true ),
						'enable'					=> $obj->post_status,
						'auto_updated' 				=> get_post_meta( $obj->ID, 'auto_updated', true ),
						'updated_on'				=> get_post_meta( $obj->ID, 'updated_on', true ),
						'auto_update'				=> get_post_meta( $obj->ID, 'auto_update', true ),
						'manualy_updated'			=> get_post_meta( $obj->ID, 'manualy_updated', true ),
					);
				}
			}
			return $currencies;
		}
		
		/*	Get all added currencies	*/
		/*	@params	null				*/
		/*	returns	Array				*/
		public function delete_currency($currency_id){
			$all_currencies = get_woocommerce_currencies();
			$deleted = wp_delete_post( $currency_id, true );
			if($deleted){
				return array(
					'success' => true,
					'message' => $all_currencies[strtoupper($deleted->post_name)].' '.esc_html__('Deleted Successfully', 'xs-mcs'),
				);
			}else{
				return array(
					'success' => false,
					'message' => $all_currencies[strtoupper($deleted->post_name)].' '.esc_html__('Not Deleted', 'xs-mcs'),
				);
			}
		}
	}
}
?>