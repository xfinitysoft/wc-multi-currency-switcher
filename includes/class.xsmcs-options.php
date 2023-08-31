<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;   //Exit if accessed directly.
}

if(!class_exists('XSMCS_Options')){
	class XSMCS_Options{
		
		public $options;
		public function __construct(){
			$this->options = $this->get_options();
		}
		/* Getting plugin options 	*/
		/* @params null				*/
		/* return Array				*/
		public function get_options(){
			$XSMCS_Currency = new XSMCS_Currency();
			$options = get_option('xs-mcs-options',true);
			if(!$options){
				$options = array(
					'enable_switcher' 					=> 'yes',
					'switcher_currencies'				=> array(),
					'auto_update_interval'				=> '3600',
					'xs_mcs_prev_auto_update_interval'	=> '3600',
					'switcher_background'				=> '000000',
					'switcher_hover_background'			=> 'FFFFFF',
					'switcher_font_color'				=> 'FFFFFF',
					'switcher_font_color_hover'			=> '000000',
					'api_key'							=> '',
				);
				foreach($XSMCS_Currency->get_currencies('enabled', true) as $c_code=>$c_name){
					$options['switcher_currencies'][] = $c_code;
				}
			}
			
			return apply_filters('xs_mcs_options', $options);
		}
		
		/*	Get Plugin Config Options	*/
		/*	@params	null				*/
		/*	returns	Array				*/
		public function config_options(){
			$XSMCS_Currency = new XSMCS_Currency();
			$date_format = get_option('date_format');
			$time_format = get_option('time_format');
			$gmt_ofset = get_option('gmt_offset')*60*60;
			$wp_crons = get_option('cron');
			foreach($wp_crons as $key=>$cron){
				if(isset($cron['xs_mcs_auto_update_currencies_values'])){
					$next_run = $key+$gmt_ofset;
					break;
				}
			}
			
			$config_options = array(
				'xs-mcs-api-key-options' => array(
					'label' 	=> esc_html__('Currency Converter API Key', 'xs-mcs'),
					'options' 	=> array(
						'xs-mcs-api-key' => array(
							'name' 			=> 'api_key',
							'label'			=> esc_html__('Api Key', 'xs-mcs'),
							'type' 			=> 'text',
							'value'			=> isset($this->options['api_key']) ? $this->options['api_key']:'',
							'wrapper_class'	=> '',
							'description'	=> esc_html__('You can get api key ', 'xs-mcs').'<a href="https://free.currencyconverterapi.com/free-api-key">'.esc_html__('here', 'xs-mcs').'</a>',
							'class' => '',
							'attributes'	=> array(
							
							)
						)
					)
				),
				'xs-mcs-currency-switcher'	=> array(
					'label'		=> esc_html__('Currency Switcher Options', 'xs-mcs'),
					'options'	=> array(
						'xs-mcs-enable-switcher' => array(
							'name'			=> 'enable_switcher',
							'label' 		=> esc_html__('Enable', 'xs-mcs'),
							'type'			=> 'checkbox',
							'value'			=> isset($this->options['enable_switcher'])?$this->options['enable_switcher']:'',
							'wrapper_class'	=> '',
							'description'	=> esc_html__('If enabled then Currency Switcher will be shown on front side.', 'xs-mcs'),
							'class' => '',
							'attributes'	=> array(
								
							)
						),
						'xs-mcs-switcher-currencies' => array(
							'name'			=> 'switcher_currencies',
							'label' 		=> esc_html__('Currencies for switcher'),
							'type'			=> 'select',
							'value'			=> isset($this->options['switcher_currencies'])?$this->options['switcher_currencies']:'',
							'wrapper_class'	=> '',
							'description'	=> esc_html__('Currencies which will be shown in Currency Switcher', 'xs-mcs'),
							'options'		=> $XSMCS_Currency->get_currencies('enabled', true),
							'class' => '',
							'attributes'    => array(
								"multiple" => "multiple"
							)
						),
						'xs-mcs-switcher-background' => array(
							'name'			=> 'switcher_background',
							'label' 		=> esc_html__('Background', 'xs-mcs'),
							'type'			=> 'text',
							'value'			=> isset($this->options['switcher_background'])?$this->options['switcher_background']:'',
							'description'	=> esc_html__('Background color currecny switcher', 'xs-mcs'),
							'wrapper_class'	=> '',
							'class' 		=> 'jscolor'
						),
						'xs-mcs-switcher-hover-background' => array(
							'name'			=> 'switcher_hover_background',
							'label' 		=> esc_html__('Background on Hover', 'xs-mcs'),
							'type'			=> 'text',
							'value'			=> isset($this->options['switcher_background'])?$this->options['switcher_hover_background']:'',
							'description'	=> esc_html__('Background color on hover for currecny switcher', 'xs-mcs'),
							'wrapper_class'	=> '',
							'class' 		=> 'jscolor'
						),
						'xs-mcs-switcher-font-color' => array(
							'name'			=> 'switcher_font_color',
							'label' 		=> esc_html__('Font Color', 'xs-mcs'),
							'type'			=> 'text',
							'value'			=> isset($this->options['switcher_font_color'])?$this->options['switcher_font_color']:'',
							'description'	=> esc_html__('Font color for currecny switcher', 'xs-mcs'),
							'wrapper_class'	=> '',
							'class' 		=> 'jscolor'
						),
						'xs-mcs-switcher-font-color-hover' => array(
							'name'			=> 'switcher_font_color_hover',
							'label' 		=> esc_html__('Font Color on Hover', 'xs-mcs'),
							'type'			=> 'text',
							'value'			=> isset($this->options['switcher_font_color_hover'])?$this->options['switcher_font_color_hover']:'',
							'description'	=> esc_html__('font color on hover for currecny switcher', 'xs-mcs'),
							'wrapper_class'	=> '',
							'class' 		=> 'jscolor'
						),
					)
				),
			);
			return $config_options;
		}
		
		/*	display option html										*/
		/*	@params	Array option atts, string option id				*/
		/*	returns	null											*/
		public function render_option_html($option, $option_id){
			ob_start();
			/*echo "<pre>";
			print_r($option);
			echo "</pre>";*/
			?>
			<tr class="<?php echo $option['wrapper_class']; ?>">
				<th><label for="<?php echo $option_id; ?>"><?php echo $option['label'] ?></label></th>
				<td>
					<?php
						switch($option['type']){
							case 'text':
							case 'number':
								?>
									<input class="<?php echo $option['class']; ?>" type="<?php echo $option['type']; ?>"
									name="<?php echo isset($option['name']) && !empty($option['name']) ? $option['name'] : $option_id ;?>"
									value="<?php echo $option['value']; ?>"
									<?php
										if(isset($option['attributes'])){
											foreach($option['attributes'] as $attr=>$val){
												echo $attr.'="'.$val.'"';
											}
										}
									?>
									/>
									<p class="xs-mcs-desc"><?php echo $option['description']; ?></p>
								<?php
								break;
								
							case 'select':
								if( isset($option['attributes']['multiple']) and ($option['attributes']['multiple'] == 'multiple') ){
								?>

									<select class="<?php echo $option['class']; ?>" name="<?php echo isset($option['name']) && !empty($option['name']) ? $option['name'] : $option_id ;?>">
										<?php
											foreach( $option['options'] as $value => $text){
												?><option value="<?php echo $value?>" <?php if($option['value'] == $value){ echo 'selected'; }; ?> ><?php echo $text; ?></option><?php
											}
										?>
									</select>
									<p class="xs-mcs-desc"><?php echo $option['description']; ?></p>
								<?php }else{ ?>
									<select class="<?php echo $option['class']; ?>" name="<?php echo isset($option['name']) && !empty($option['name']) ? $option['name'] : $option_id ;?>" >
										<?php
											foreach( $option['options'] as $value => $text){
												?><option value="<?php $value?>" <?php selected( strtoupper($option['value']), $value ); ?> ><?php echo $text; ?></option><?php
											}
										?>
									</select>
									<p class="xs-mcs-desc"><?php echo $option['description']; ?></p>
								<?php }
								break;
								
							case 'checkbox':
								?>
									<input class="<?php echo $option['class']; ?>" type="checkbox"
										name="<?php echo isset($option['name']) && !empty($option['name']) ? $option['name'] : $option_id ;?>"
										<?php checked( $option['value'], 'yes', true ); ?>
									>
									<p class="xs-mcs-desc"><?php echo $option['description']; ?></p>
								<?php
								break;
									
						}
					?>
				</td>
			</tr>
			<?php
			echo ob_get_clean();
		}
		
		/*	Save options on submit options form	*/
		/*	@params	none						*/
		/*	returns	none						*/
		public function save_options(){
			if( isset($_POST['xs-mcs-save-settings-nounce']) and wp_verify_nonce($_POST['xs-mcs-save-settings-nounce'], 'xs-mcs-save-settings') ){
				$options = array();
				$options['api_key'] = sanitize_text_field($_POST['api_key']);
				$options['enable_switcher'] = isset($_POST['enable_switcher']) ? 'yes' : 'no';
				$options['switcher_currencies'] = sanitize_text_field($_POST['switcher_currencies']);
				$options['auto_update_interval'] = 3600;
				$options['switcher_background'] = sanitize_text_field($_POST['switcher_background']);
				$options['switcher_hover_background'] = sanitize_text_field($_POST['switcher_hover_background']);
				$options['switcher_font_color'] = sanitize_text_field($_POST['switcher_font_color']);
				$options['switcher_font_color_hover'] = sanitize_text_field($_POST['switcher_font_color_hover']);
				$options['xs_mcs_prev_auto_update_interval'] = 3600;
				
				// Hook before options saved
				// args available at this hook are previous options and new options
				do_action('xs-mcs-before-options-saved', $this->options, $options);
				
				update_option('xs-mcs-options', $options);
				
				// update options for current form too
				$this->options = $this->get_options();
				
				// Hook before options saved
				// args available at this hook: updated options
				do_action('xs-mcs-options-saved', $this->options);
				
				wp_send_json(
					array(
						'success' 	=> true,
						'msg'		=> esc_html__( 'Options Saved', 'xs-mcs' ),
					)
				);
			}else{
				wp_send_json(
					array(
						'success' 	=> false,
						'msg'		=> esc_html__( 'Nounce not matched.', 'xs-mcs' ),
					)
				);
			}
		}
	}
}