<?php

// Creating the widget 
class xs_mcs_widget extends WP_Widget {
	/*	Constructor	*/
	function __construct() {
		
		// calling parent constructor
		parent::__construct(
			'xs_mcs_widget', 
			esc_html__( 'Multi Currency Switcher', 'xs-mcs' ), 
			array( 
				'description' => esc_html__( 'Multi Currency Switcher - Currency Switcher Widget to show currency swithcer.', 'xs-mcs' )
			) 
		);
	}
	 
	public function widget( $args, $instance ){
		$title = apply_filters( 'widget_title', $instance['title'] );
		 
		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];
		 
		echo xs_mcs_get_currency_switcher_html();
		
		echo $args['after_widget'];
	}
			 
	public function form( $instance ){
		$XSMCS_Currency = new XSMCS_Currency();
		$xsmcs_currencies = $XSMCS_Currency->get_currencies('publish', true);
		if( isset( $instance[ 'title' ] ) ){
			$title = $instance[ 'title' ];
		}else{
			$title = '';
		}
		if( isset( $instance[ 'switcher_currencies' ] ) ) {
			$switcher_currencies = $instance[ 'switcher_currencies' ];
		}else{
			$XSMCS_Options = new XSMCS_Options();
			$switcher_currencies = $XSMCS_Options->options['switcher_currencies'];
		}
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'xs-mcs' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'switcher_currencies' ); ?>"><?php esc_html_e( 'Currencies:', 'xs-mcs' ); ?></label> 
			<select class="widefat" id="<?php echo $this->get_field_id( 'switcher_currencies' ); ?>" name="<?php echo $this->get_field_name( 'switcher_currencies' ); ?>[]" multiple="multiple">
				<?php foreach($xsmcs_currencies as $c_code => $c_name){ ?>
					<option value="<?php echo $c_code; ?>" <?php if(in_array($c_code, $switcher_currencies)){echo 'selected';} ?>><?php echo $c_name; ?></option>
				<?php } ?>
			</select>
		</p>
		<?php 
	}
		 
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ){
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['switcher_currencies'] = ( ! empty( $new_instance['switcher_currencies'] ) ) ? $new_instance['switcher_currencies'] : array();
		return $instance;
	}
}
?>