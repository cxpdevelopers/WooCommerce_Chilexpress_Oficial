<?php

if ( ! class_exists( 'Chilexpress_Woo_Oficial_Shipping_Method' ) ) {
	class Chilexpress_Woo_Oficial_Shipping_Method extends WC_Shipping_Method {
		/**
		 * Constructor for your shipping class
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->id                 = 'chilexpress_woo_oficial';
			$this->method_title       = 'Chilexpress';
			$this->method_description = 'Envios con Chilexpress';

			$this->coverage_data = new Chilexpress_Woo_Oficial_Coverage();

			// Availability & Countries
            $this->availability = 'including';
            $this->countries = array(
                'CL' // Chile
            );

			$this->init();

			$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
			$this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : 'Envios con Chilexpress';
		}

		/**
		 * Init your settings
		 *
		 * @access public
		 * @return void
		 */
		function init() {
			// Load the settings API
			$this->init_form_fields(); 
			$this->init_settings(); 

			// Save settings in admin if you have any defined
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			add_filter( 'woocommerce_states', array( $this, 'get_states' ) );
			add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_true' );
			add_filter( 'woocommerce_shipping_calculator_enable_postcode', '__return_false' );
			add_filter( 'woocommerce_checkout_fields', array($this, 'chilexpress_woo_oficial_change_city_to_dropdown') );
			add_filter( 'woocommerce_shipping_fields', array($this, 'chilexpress_woo_oficial_change_city_to_dropdown') );
			add_action( 'woocommerce_review_order_before_cart_contents', array($this, 'chilexpress_woo_oficial_validate_order') , 10 );
			add_action( 'woocommerce_after_checkout_validation', array($this, 'chilexpress_woo_oficial_validate_order') , 10 );
			add_filter( 'woocommerce_default_address_fields' , array($this, 'override_postcode_validation') );
			add_filter( 'woocommerce_checkout_fields' , array($this, 'checkout_fields_override_postcode_validation') );
			add_filter( 'woocommerce_default_address_fields', array($this, 'reorder_fields') );
			add_filter( 'woocommerce_checkout_fields' , array($this,'custom_override_checkout_fields') );

			add_action( 'woocommerce_checkout_update_order_meta', array($this, 'custom_checkout_field_update_order_meta' ));

		}


		/**
		 * Define settings field for this shipping
		 * @return void 
		 */
		function init_form_fields() { 
			$this->form_fields = array(

				'enabled' => array(
					'title' => 'Habilitar',
					'type' => 'checkbox',
					'description' => 'Habilitar este método de envío.',
					'default' => 'yes'
				),

				'title' => array(
					'title' => 'Title',
					'type' => 'text',
					'description' =>  'Titulo a mostrar en el sitio',
					'default' => 'Envios con Chilexpress'
				)

			);

        }

		/**
		 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
		 *
		 * @access public
		 * @param mixed $package
		 * @return void
		 */
		public function calculate_shipping( $package = array() ) {
			// We will add the cost, rate and logics in here
			$weight = 0;
			$biggest_product = false;
			$biggest_size = 0;

			$i = 0;
            foreach ( $package['contents'] as $item_id => $values ) 
            { 
                $_product = $values['data']; 
                $dimensions = $_product->get_dimensions(false);

                if ($dimensions["width"]!="" && $dimensions["height"]!="" && $dimensions["length"]!="") {
                	if( $biggest_size < $_product->get_height() * $_product->get_width() *$_product->get_length())
                	{
                		$biggest_size = $_product->get_height() * $_product->get_width() * $_product->get_length();
                		$biggest_product = $_product;
                	}
                	$i++;
                }
                if ($_product->get_weight() != "") {
                	$weight = $weight + $_product->get_weight() * $values['quantity']; 
                }
            }

            $options = get_option( 'chilexpress_woo_oficial_general' );


            $api = new Chilexpress_Woo_Oficial_API();
            if ($biggest_product) {
            	$response = $api->obtener_cotizacion($options['comuna_origen'],  WC()->checkout->get_value('shipping_city') , $weight, $biggest_product->get_height(), $biggest_product->get_width(), $biggest_product->get_length(), 1000);
            } else {
            	$rate = array(
                    'id' => $this->id,
                    'label' => "Ningun producto que eligió tiene fijado su tamaño, comuniquese con el administrador",
                    'cost' => -1
                );
                $this->add_rate( $rate );
                return;
            }
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
			  	$rate = array(
                    'id' => $this->id,
                    'label' => "Something went wrong: $error_message",
                    'cost' => -1
                );
                $this->add_rate( $rate );
			} else {

				$lowest_service = NULL;

				foreach($response as $soption)
				{
					if($soption->serviceTypeCode >= 3)
					{
						if($lowest_service == NULL)
						{
							$lowest_service = $soption;
						}
						else
						{
							if($lowest_service->serviceTypeCode > $soption->serviceTypeCode)
							{
								$lowest_service = $soption;
							}
						}
					}
				}
				   
				$rate = array(
				'id' => $this->id.':'. $lowest_service->serviceTypeCode,
				'label' => 'Chilexpress - '. $lowest_service->serviceDescription,
				'cost' => $lowest_service->serviceValue
				);

				$this->add_rate( $rate );
			}
		}

		public function chilexpress_woo_oficial_validate_order( $posted )   {

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


		static function get_states( $states ) {
				$coverage_data = new Chilexpress_Woo_Oficial_Coverage();

				$regiones = $coverage_data->obtener_regiones();
				$states['CL'] = array();
				foreach ($regiones as $key => $value) {
					$states['CL'][$key] = $value;
				}

				return $states;
		}

		public function override_postcode_validation( $address_fields ) {
			$address_fields['postcode']['required'] = false;
			return $address_fields;
		}

		public function checkout_fields_override_postcode_validation( $fields ) {
			$fields['billing']['billing_postcode']['required'] = false;
			$fields['shipping']['shipping_postcode']['required'] = false;
			return $fields;
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

		function custom_override_checkout_fields( $fields ) {
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


	}
}
