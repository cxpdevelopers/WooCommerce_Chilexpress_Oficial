<?php 

if ( ! class_exists( 'Chilexpress_Woo_Oficial_API' ) ) {
	class Chilexpress_Woo_Oficial_API {
		/**
		 * Constructor for your shipping class
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			$this->id                 = 'chilexpress_woo_oficial';
			$this->init();
			
			$this->api_staging_base_url = 'https://testservices.wschilexpress.com/';
			$this->api_production_base_url = 'https://services.wschilexpress.com/';

			$module_options = get_option( 'chilexpress_woo_oficial' );
			$general_options = get_option( 'chilexpress_woo_oficial' );

			if ($module_options['ambiente'] == 'production') {
				
				$this->api_base_url = $this->api_production_base_url;
			} else
			{
				$this->api_base_url = $this->api_staging_base_url;
			}

			$this->api_key_georeferencia = isset($module_options['api_key_cotizador_value'])? $module_options['api_key_cotizador_value']:'';
			$this->api_key_cobertura = isset($module_options['api_key_georeferencia_value'])? $module_options['api_key_georeferencia_value']:'';
			$this->api_key_ot = isset($module_options['api_key_generacion_ot_value'])? $module_options['api_key_generacion_ot_value']:'';

			$this->api_geo_enabled = $module_options['api_key_georeferencia_enabled'] || false;
			$this->api_ot_enabled = $module_options['api_key_generacion_ot_enabled'] || false;
		}

		public function obtener_regiones() {
			$url = $this->api_base_url . 'georeference/api/v1.0/regions';
			$response = wp_remote_post( $url, array(
					'method' => 'GET',
					'timeout' => 2,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(
						'Content-Type' => 'application/json',
		    			'Ocp-Apim-Subscription-Key' => $this->api_key_cobertura
					),
					'body' => '',
					'cookies' => array()
				    )
				);
			if ( is_wp_error( $response ) ) {
				return $response;
			} else if ($response['response']['code'] == 200) {
				$data = json_decode($response['body']);
				$regiones = array();

				foreach ($data->regions as $region) {
					$regiones[$region->regionId] = $region->regionName;
				}
				return $regiones;
			}
		}

		public function obtener_comunas_desde_region($codigo_region = "R1") {
			$url = $this->api_base_url . 'georeference/api/v1.0/coverage-areas?RegionCode='.$codigo_region.'&type=1';
			
			$response = wp_remote_post( $url, array(
					'method' => 'GET',
					'timeout' => 2,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(
						'Content-Type' => 'application/json',
		    			'Ocp-Apim-Subscription-Key' => $this->api_key_cobertura
					),
					'body' => '',
					'cookies' => array()
				    )
				);
			if ( is_wp_error( $response ) ) {
				return $response;
			} else if ($response['response']['code'] == 200) {
				$data = json_decode($response['body']);
				$comunas = array();

				foreach ($data->coverageAreas as $comuna) {
					$comunas[$comuna->countyCode] = $comuna->coverageName;
				}
				return $comunas;
			}
		}

		public function obtener_cotizacion($comuna_origen, $comuna_destino, $weight = 1, $height = 1, $width = 1, $length = 1, $declaredWorth = 1000) {

			$payload = array(
				"originCountyCode" =>	$comuna_origen,
				"destinationCountyCode" => $comuna_destino,
				"package" => array(
					"weight" =>	$weight,
					"height" =>	$height,
					"width" =>	$width,
					"length" =>	$length
				),
				"productType" => 3,
				"contentType" => 1,
				"declaredWorth" => $declaredWorth,
				"deliveryTime" => 0
			);

			$url = $this->api_base_url."rating/api/v1.0/rates/courier";
			$response = wp_remote_post( $url, array(
				'method' => 'POST',
				'timeout' => 10,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(
					'Content-Type' => 'application/json',
	    			'Ocp-Apim-Subscription-Key' => $this->api_key_georeferencia
				),
				'body' => json_encode($payload),
				'cookies' => array()
			    )
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			} else {
			   	if ($response['response']['code'] == 200) {
			   		$json_response = json_decode($response['body']);

			   		return $json_response->data->courierServiceOptions;
			    }
			}
			
		}

		public function generar_ot($payload) {

			$url = $this->api_base_url."/transport-orders/api/v1.0/transport-orders";	
			$response = wp_remote_post( $url, array(
				'method' => 'POST',
				'timeout' => 5,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(
					'Content-Type' => 'application/json',
	    			'Ocp-Apim-Subscription-Key' => $this->api_key_ot
				),
				'body' => json_encode($payload),
				'cookies' => array()
			    )
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			} else {
			   	if ($response['response']['code'] == 200) {
			   		$json_response = json_decode($response['body']);
			   		return json_decode($response['body']);
			    }
			}

			
		}

		public function obtener_estado_ot($trackingId, $reference, $rut ) {
			$payload = array(
				"reference"=> $reference,
				"transportOrderNumber"=> $trackingId,
				"rut"=> $rut,
				"showTrackingEvents" => 1
			);
			$url = $this->api_base_url."/transport-orders/api/v1.0/tracking";
			$response = wp_remote_post( $url, array(
				'method' => 'POST',
				'timeout' => 2,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(
					'Content-Type' => 'application/json',
	    			'Ocp-Apim-Subscription-Key' => $this->api_key_ot
				),
				'body' => json_encode($payload),
				'cookies' => array()
			    )
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			} else {
			   	if ($response['response']['code'] == 200) {
			   		$json_response = json_decode($response['body']);
			   		return json_decode($response['body']);
			    }
			}
		}

		/**
		 * Init your settings
		 *
		 * @access public
		 * @return void
		 */
		function init() {
			
			#add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			#add_filter( 'woocommerce_states', array( $this, 'get_states' ) );
		}

		

	}
}