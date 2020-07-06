<?php

/**
 * The public-facing functionality of the plugin.
 *
 
 * @since      1.0.0
 *
 * @package    Chilexpress_Woo_Oficial
 * @subpackage Chilexpress_Woo_Oficial/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Chilexpress_Woo_Oficial
 * @subpackage Chilexpress_Woo_Oficial/public
 * @author     Chilexpress
 */
class Chilexpress_Woo_Oficial_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Chilexpress_Woo_Oficial_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Chilexpress_Woo_Oficial_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( is_dir( WP_PLUGIN_DIR . '/woocommerce-3.9.2' ) ) {
			$backbone_path = '/woocommerce-3.9.2';
		}
		elseif( is_dir( WP_PLUGIN_DIR . '/woocommerce') )
		{
			$backbone_path = '/woocommerce';
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/chilexpress-woo-oficial-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . '..'.$backbone_path.'assets/css/admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		global $woocommerce;
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Chilexpress_Woo_Oficial_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Chilexpress_Woo_Oficial_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/chilexpress-woo-oficial-public.js', array( 'jquery' ), $this->version, false );


		$backbone_url = '';
		if ($woocommerce) {
			$backbone_url =  $woocommerce->plugin_url().'/assets/js/admin/backbone-modal.js';	
		}

		wp_enqueue_script(
		   'backbone-modal',
		   $backbone_url,
		   array('jquery', 'wp-util', 'backbone')
		);

		wp_localize_script( $this->plugin_name, 'woocommerce_chilexpress', array(
	        'base_url'    => plugin_dir_url( __FILE__ ),
	        'nonce'  => wp_create_nonce( 'cwo-clxp-ajax-nonce' )
    	) );

	}

	public function chilexpress_woo_oficial_change_city_to_dropdown( $fields ) {	
		$state = WC()->checkout->get_value('billing_state');
		if (!$state) {
			$state = 'R1';
		}

		if (isset($fields['shipping'])){

			$options = array();
			$coverage_data = new Chilexpress_Woo_Oficial_Coverage();
			$comunas = $coverage_data->obtener_comunas($state);
			foreach ($comunas as $key => $value) {
				$options[$key] = $value;
			}

			$city_args = wp_parse_args( array(
				'type' => 'select',
				'options' => $options,
				'input_class' => array(
					'wc-enhanced-select',
				)
			), $fields['shipping']['shipping_city'] );
			$fields['shipping_state']['priority'] = '65';
			$fields['shipping']['shipping_city'] = $city_args;
			$fields['billing']['billing_city'] = $city_args; // Also change for billing field

			// echo '<pre>'.print_r($fields, true).'</pre>';

			wc_enqueue_js( "
			jQuery( ':input.wc-enhanced-select' ).filter( ':not(.enhanced)' ).each( function() {
				var select2_args = { minimumResultsForSearch: 5 };
				jQuery( this ).select2( select2_args ).addClass( 'enhanced' );
			});" );

		}
		return $fields;
	}

	public function checkout_fields_override( $fields ) {

		$fields['billing']['billing_postcode']['required'] = false;
		$fields['shipping']['shipping_postcode']['required'] = false;

		$fields['shipping']['shipping_address_3'] = array(
			'label'     => __('Complemento', 'woocommerce'),
			'placeholder'   => _x('Complemento', 'placeholder', 'woocommerce'),
			'required'  => false,
			'class'     => array('form-row-wide'),
			'clear'     => true,
			'priority'  => 62,
		);
		$fields['billing']['billing_address_3'] = array(
			'label'     => __('Complemento', 'woocommerce'),
			'placeholder'   => _x('Complemento', 'placeholder', 'woocommerce'),
			'required'  => false,
			'class'     => array('form-row-wide'),
			'clear'     => true,
			'priority'  => 62,
		);

		$fields['shipping']['shipping_address_3'] = array(
			'label'     => __('Complemento', 'woocommerce'),
			'placeholder'   => _x('Complemento', 'placeholder', 'woocommerce'),
			'required'  => false,
			'class'     => array('form-row-wide'),
			'clear'     => true,
			'priority'  => 62,
		);
		$fields['billing']['billing_address_3'] = array(
			'label'     => __('Complemento', 'woocommerce'),
			'placeholder'   => _x('Complemento', 'placeholder', 'woocommerce'),
			'required'  => false,
			'class'     => array('form-row-wide'),
			'clear'     => true,
			'priority'  => 62,
		);
		
		return $fields;
	}

	function custom_checkout_field_update_order_meta($order_id) {
		if ( ! empty( $_POST['billing_address_3'] ) ) {
			update_post_meta( $order_id, 'billing_address_3', sanitize_text_field( $_POST['billing_address_3'] ) );
		}
		if ( ! empty( $_POST['shipping_address_3'] ) ) {
			update_post_meta( $order_id, 'shipping_address_3', sanitize_text_field( $_POST['shipping_address_3'] ) );
		}
	}

	public function reorder_fields($fields) {
		// unset($fields['company']);
		// var_dump($fields); die();
					$fields['address_1']['label'] = 'Nombre de la Calle';
					$fields['address_1']['required'] = true;
		$fields['address_2']['label'] = 'N&uacute;mero';
					$fields['address_2']['placeholder'] = 'Número';
		$fields['address_2']['required'] = true;
		$fields['state']['priority'] = 42;
		$fields['city']['priority'] = 43;
		$fields['email']['priority'] = 22;
		return $fields;
	}

	public function override_postcode_validation( $address_fields ) {
		$address_fields['postcode']['required'] = false;
		return $address_fields;
	}

	public function chilexpress_woo_oficial_validate_order( $posted ) {

		$packages = WC()->shipping->get_packages();

		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
		if( is_array( $chosen_methods ) && in_array( 'chilexpress_woo_oficial', $chosen_methods ) ) {
			foreach ( $packages as $i => $package ) {

				if ( $chosen_methods[ $i ] != "chilexpress_woo_oficial" ) {
					continue;
				}
				$Chilexpress_Woo_Oficial_Shipping_Method = new Chilexpress_Woo_Oficial_Shipping_Method(); 
				$weight = 0;
				foreach ( $package['contents'] as $item_id => $values ) 
				{ 
					$_product = $values['data']; 
					$weight = $weight + $_product->get_weight() * $values['quantity']; 
				}

				$weight = wc_get_weight( $weight, 'kg' );
				/*
				if( $weight > $weightLimit ) {

						$message = sprintf( 'Lo sentimos, %d excede el peso de %d kg para %s', $weight, $weightLimit, $Chilexpress_Woo_Oficial_Shipping_Method->title );
						$messageType = "error";

						if( ! wc_has_notice( $message, $messageType ) ) {
							wc_add_notice( $message, $messageType );
						}
				} */
			}       
		} 
	}

	static function get_states( $states ) {
		$coverage_data = new Chilexpress_Woo_Oficial_Coverage();

		$regiones = $coverage_data->obtener_regiones();
		$states['CL'] = array();
		foreach ($regiones as $key => $value) {
			$states['CL'][$key] = $value;
		}

		return $states;
	}

	public function rewrite_pdf_label( $wp_rewrite ){
		$wp_rewrite->rules = array_merge(
			['download-order-label/(\d+)/?$' => 'index.php?order_label=$matches[1]'],
			$wp_rewrite->rules
		);
	}
	
	public function add_pdf_label_query_vars( $query_vars ){
		$query_vars[] = 'order_label';
		return $query_vars;
	}

	function template_redirect_pdf_label(){
		$order_label = intval( get_query_var( 'order_label' ) );
		if ( $order_label ) {
			include plugin_dir_path( __FILE__ ) . '../print-label.php';
			die;
		}
	}
	
}
