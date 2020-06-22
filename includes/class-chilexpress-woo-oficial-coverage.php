<?php

/**
 * Datos de Cobertura (Región/comuna)
 *
 
 * @since      1.0.0
 *
 * @package    Chilexpress_Woo_Oficial
 * @subpackage Chilexpress_Woo_Oficial/includes
 */

/**
 * Coverage data for Chilexpress
 *
 *
 * @since      1.0.0
 * @package    Chilexpress_Woo_Oficial
 * @subpackage Chilexpress_Woo_Oficial/includes
 * @author     Chilexpress
 */
class Chilexpress_Woo_Oficial_Coverage {

	private function get_local_file_contents( $file_path ) {
	    ob_start();
	    include $file_path;
	    $contents = ob_get_clean();

	    return $contents;
	}

	public function obtener_regiones() {

		$api = new Chilexpress_Woo_Oficial_API();
		$response = $api->obtener_regiones();
		if (is_wp_error($response) || count($response)) {

			$directory = trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/data/regiones/' );

		    // Define the URL
		    $file_path = $directory . 'regiones.json';

		    if (file_exists($file_path)) {
		    	$raw_json = $this->get_local_file_contents( $file_path );
			    $data = json_decode( $raw_json );
			    
			    $regiones = array();

				foreach ($data->regions as $region) {
					$regiones[$region->regionId] = $region->regionName;
				}
				return $regiones;
			} else {
				return array();
			}
		} else {
			return $response;
		}

	}

	public function obtener_comunas($codigo_region = "") {
		if (!$codigo_region || $codigo_region == "") {
			return array();
		}
		$api = new Chilexpress_Woo_Oficial_API();
		$response = $api->obtener_comunas_desde_region($codigo_region);

		if (is_wp_error($response) || count($response)) {
			$directory = trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/data/comunas/' );

		    // Define the URL
		    $file_path = $directory . $codigo_region .".json";

		    if (file_exists($file_path)) {
		    	$raw_json = $this->get_local_file_contents( $file_path );
			    $data = json_decode( $raw_json );
			    
			    $comunas = array();

				foreach ($data->coverageAreas as $comuna) {
					$comunas[$comuna->countyCode] = $comuna->coverageName;
				}
				return $comunas;
			} else {
				return array();
			}
		} else {
			return $response;
		}
	}
	

}
