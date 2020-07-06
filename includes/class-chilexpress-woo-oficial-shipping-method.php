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
	}
}
