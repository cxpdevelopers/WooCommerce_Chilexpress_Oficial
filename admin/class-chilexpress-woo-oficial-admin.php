<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Chilexpress_Woo_Oficial
 * @subpackage Chilexpress_Woo_Oficial/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Chilexpress_Woo_Oficial
 * @subpackage Chilexpress_Woo_Oficial/admin
 * @author     Chilexpress
 */
class Chilexpress_Woo_Oficial_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->coverage_data = new Chilexpress_Woo_Oficial_Coverage();

		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_filter( 'woocommerce_admin_order_actions', array($this, 'add_custom_order_status_actions_button'), 100, 2 );
		// we add the style for the custom order actuion buttons	
		add_action( 'admin_head', array($this, 'add_custom_order_status_actions_button_css') );

		// we add the tracking column
		add_filter('manage_edit-shop_order_columns', array($this, 'wc_order_columns'));
		// we add the content of the tracking column
		add_action('manage_shop_order_posts_custom_column', array($this, 'wc_order_column'), 10, 2);

		// we add the tracking column
		add_filter( 'woocommerce_my_account_my_orders_columns', array($this, 'wc_add_my_account_orders_column') );	
		// we add the content of the tracking column
		add_action( 'woocommerce_my_account_my_orders_column_order-tracking', array($this, 'wc_user_tracking_column') );
		// we insert the Modal template on the admin footer
		add_action( 'admin_footer', array($this, 'wc_insert_footer') );
		add_action( 'wp_footer', array($this, 'wc_insert_footer'));
	}


	public function wc_add_my_account_orders_column( $columns ) {

		$new_columns = array();

		foreach ( $columns as $key => $name ) {

			$new_columns[ $key ] = $name;

			// add ship-to after order status column
			if ( 'order-actions' === $key ) {
				$new_columns['order-tracking'] = 'Tracking';
			}
		}

		return $new_columns;
	}



	public function wc_user_tracking_column( $order ) {
		$transportOrderNumbers = $order->get_meta( 'transportOrderNumbers');
		if (is_array($transportOrderNumbers)) {
			$out = array();
			foreach($transportOrderNumbers as $transportOrderNumber) {
				$out[] = '<a href="javascript:;" class="tracking-link" data-pid="'.$order->get_id().'" data-ot="'.$transportOrderNumber.'">'.$transportOrderNumber.'</a>';
			}
			echo implode(", ", $out);
		} else {
			echo '-';
		}
	}

	public function wc_insert_footer() {
	    require_once plugin_dir_path( __FILE__ ) . 'partials/chilexpress-woo-oficial-admin-tracking-template.php';
	}
	

	public function wc_order_columns( $columns ) {
	    $columns["tracking"] = "Tracking";
	    return $columns;
	}

	public function wc_order_column( $colname, $order_id ) {
		if ( $colname == 'tracking') {
			$order = wc_get_order( $order_id );
			$transportOrderNumbers = $order->get_meta( 'transportOrderNumbers');
			if (is_array($transportOrderNumbers)) {
				$out = array();
				foreach($transportOrderNumbers as $transportOrderNumber) {
					$out[] = '<a href="javascript:;" data-pid="'.$order_id.'" data-ot="'.$transportOrderNumber.'">'.$transportOrderNumber.'</a>';
				}
				echo implode(", ", $out);
			}
		}
	}




	// We add our custom buttons when the order is marked as completed
	public function add_custom_order_status_actions_button( $actions, $order ) {
	    // Display the button for all orders that have a 'processing' status
	    if ( $order->has_status( array( 'completed' ) ) ) {

	        // The key slug defined for your action button
	        $action_slug = 'generar_ot';
	        $action_slug2 = 'imprimir_ot';

			$ot_status = $order->get_meta('ot_status');
			$transportOrderNumbers = $order->get_meta( 'transportOrderNumbers');
	        // Set the action button
	        if(!$ot_status || ($ot_status == 'created' && count($transportOrderNumbers) == 0)){
		        $actions[$action_slug] = array(
		            'url'       => wp_nonce_url( admin_url( 'admin.php?page=chilexpress_woo_oficial_generar_ot&action=generar_ot&order_id=' . $order->get_id() ), 'generar-ot' ),
		            'name'      => 'Generar OT',
		            'action'    => $action_slug,
		        );
	        }
	        if($ot_status == 'created' && count($transportOrderNumbers) > 0) {
	        	// Set the action button
		        $actions[$action_slug2] = array(
		            'url'       =>  wp_nonce_url( admin_url( 'admin.php?page=chilexpress_woo_oficial_generar_ot&action=imprimir_ot&order_id=' . $order->get_id()) , 'generar-ot'),
		            'name'      => 'Imprimir OT',
		            'action'    => $action_slug2,
		        );
	        }
	    }
	    return $actions;
	}


	public function page_init() {
		register_setting(
            'chilexpress-woo-oficial', // Option group
            'chilexpress_woo_oficial'// , // Option name
            // array( $this, 'sanitize' ) // Sanitize
        );

		add_settings_section(
	        'habilitar_modulo_georeferencia_section_1',
	        '&nbsp;',
	        array($this, 'stp_api_settings_section_callback'),
	        'chilexpress-woo-oficial'
	    );


        add_settings_field(
	        'api_key_georeferencia_enabled',
	        'Módulo de Georeferencia',
	        array($this, 'chilexpress_woo_oficial_field_1_render'),
	        'chilexpress-woo-oficial',
	        'habilitar_modulo_georeferencia_section_1'
	    );

	    add_settings_field(
	        'api_key_georeferencia_value',
	        'API KEY Georeferencia',
	        array($this, 'chilexpress_woo_oficial_field_2_render'),
	        'chilexpress-woo-oficial',
	        'habilitar_modulo_georeferencia_section_1'
	    );
	    //////
	    add_settings_field(
	        'api_key_generacion_ot_enabled',
	        'Módulo de generacion de OT',
	        array($this, 'chilexpress_woo_oficial_field_3_render'),
	        'chilexpress-woo-oficial',
	        'habilitar_modulo_georeferencia_section_1'
	    );
	    add_settings_field(
	        'api_key_generacion_ot_value',
	        'API KEY Órdenes de transporte',
	        array($this, 'chilexpress_woo_oficial_field_4_render'),
	        'chilexpress-woo-oficial',
	        'habilitar_modulo_georeferencia_section_1'
	    );
	    /**/
	    add_settings_field(
	        'api_key_cotizacion_enabled',
	        'Módulo de Cotización',
	        array($this, 'chilexpress_woo_oficial_field_5_render'),
	        'chilexpress-woo-oficial',
	        'habilitar_modulo_georeferencia_section_1'
	    );
	    add_settings_field(
	        'api_key_cotizacion_value',
	        'API KEY Módulo de Cotización',
	        array($this, 'chilexpress_woo_oficial_field_6_render'),
	        'chilexpress-woo-oficial',
	        'habilitar_modulo_georeferencia_section_1'
	    );
	    /**/

	    add_settings_field(
	        'ambiente',
	        'Ambiente',
	        array($this, 'chilexpress_woo_oficial_field_7_render'),
	        'chilexpress-woo-oficial',
	        'habilitar_modulo_georeferencia_section_1'
	    );
	    /***************************************/

	    register_setting(
            'chilexpress-woo-oficial-general', // Option group
            'chilexpress_woo_oficial_general'// , // Option name
            // array( $this, 'sanitize' ) // Sanitize
        );

	    /***************************************/

		add_settings_section(
	        'origen_section',
	        'Datos de Origen',
	        array($this, 'stp_api_settings_section_callback'),
	        'chilexpress-woo-oficial-general'
	    );

	    add_settings_field(
	        'region_origen',
	        'Region de Origen',
	        array($this, 'region_origen_render'),
	        'chilexpress-woo-oficial-general',
	        'origen_section'
	    );
	    add_settings_field(
	        'codigo_comuna_origen',
	        'Código de comuna de origen',
	        array($this, 'comuna_origen_render'),
	        'chilexpress-woo-oficial-general',
	        'origen_section'
	    );
	    
	    add_settings_field(
	        'numero_tcc_origen',
	        'Número TCC',
	        array($this, 'numero_tcc_origen_render'),
	        'chilexpress-woo-oficial-general',
	        'origen_section'
	    );

	    /***************************************/

	    add_settings_section(
	        'remitente_section',
	        'Datos del Remitente',
	        array($this, 'stp_api_settings_section_callback'),
	        'chilexpress-woo-oficial-general'
	    );

	    add_settings_field(
	        'nombre_remitente',
	        'Nombre',
	        array($this, 'nombre_remitente_render'),
	        'chilexpress-woo-oficial-general',
	        'remitente_section'
	    );

	    add_settings_field(
	        'telefono_remitente',
	        'Teléfono',
	        array($this, 'telefono_remitente_render'),
	        'chilexpress-woo-oficial-general',
	        'remitente_section'
	    );
	    add_settings_field(
	        'email_remitente',
	        'E-mail',
	        array($this, 'email_remitente_render'),
	        'chilexpress-woo-oficial-general',
	        'remitente_section'
	    );
	    add_settings_field(
	        'rut_seller_remitente',
	        'Rut Seller',
	        array($this, 'rut_seller_remitente_render'),
	        'chilexpress-woo-oficial-general',
	        'remitente_section'
	    );

	    add_settings_field(
	        'rut_marketplace_remitente',
	        'Rut marketplace',
	        array($this, 'rut_marketplace_remitente_render'),
	        'chilexpress-woo-oficial-general',
	        'remitente_section'
	    );
	    /***************************************/

	    add_settings_section(
	        'devolucion_section',
	        'Dirección de devolución',
	        array($this, 'stp_api_settings_section_callback'),
	        'chilexpress-woo-oficial-general'
	    );

	    add_settings_field(
	        'region_origen',
	        'Region de devolución:',
	        array($this, 'region_devolucion_render'),
	        'chilexpress-woo-oficial-general',
	        'devolucion_section'
	    );

	    add_settings_field(
	        'codigo_comuna_devolucion',
	        'Código de comuna:',
	        array($this, 'comuna_devolucion_render'),
	        'chilexpress-woo-oficial-general',
	        'devolucion_section'
	    );

	    add_settings_field(
	        'calle_devolucion',
	        'Nombre de la calle',
	        array($this, 'calle_devolucion_render'),
	        'chilexpress-woo-oficial-general',
	        'devolucion_section'
	    );

	    add_settings_field(
	        'numero_calle_devolucion',
	        'Número de la dirección',
	        array($this, 'numero_calle_devolucion_render'),
	        'chilexpress-woo-oficial-general',
	        'devolucion_section'
	    );
	    add_settings_field(
	        'complemento_devolucion',
	        'Complemento',
	        array($this, 'complemento_devolucion_render'),
	        'chilexpress-woo-oficial-general',
	        'devolucion_section'
	    );
   
	}
	public function stp_api_settings_section_callback(  ) {
   		echo '';
	}

	public function chilexpress_woo_oficial_field_1_render() {
		$options = get_option( 'chilexpress_woo_oficial' );
	    ?>
	    <label for="api_key_georeferencia_enabled">
	    	<input type='checkbox' id="api_key_georeferencia_enabled" name='chilexpress_woo_oficial[api_key_georeferencia_enabled]' value='1' <?php if($options['api_key_georeferencia_enabled'] == '1') echo 'checked="checked"'; ?>> Habilitar
	    	<br /><small>Necesitas este módulo para poder obtener información actualizada de Regiones y Comunas, crea tu API KEY <a href="https://developers.wschilexpress.com/products/georeference/subscribe" target="_blank">aquí</a>.</small>
		</label>
	    <?php
	}
	public function chilexpress_woo_oficial_field_2_render() {
		$options = get_option( 'chilexpress_woo_oficial' );
		if (isset($options['api_key_georeferencia_value']) && !empty($options['api_key_georeferencia_value']) && trim($options['api_key_georeferencia_value']) != "" ) {
			$value = $options['api_key_georeferencia_value'];
		} else {
			$value = "134b01b545bc4fb29a994cddedca9379";
		}
	    ?>
	    <input type='text' name='chilexpress_woo_oficial[api_key_georeferencia_value]' value='<? echo $value;?>' class="regular-text"> 
	    <br /><small>Puedes encontrar esta Api Key, bajo el producto Coberturas en tu página de <a href="https://developers.wschilexpress.com/developer" target="_blank">perfil</a>.</small>
	    <?php
	}

	public function chilexpress_woo_oficial_field_3_render() {
		$options = get_option( 'chilexpress_woo_oficial' );
	    ?>
	    <label for="chilexpress_woo_oficial[api_key_generacion_ot_enabled]">
	    	<input type='checkbox' id="generacion_ot" name='chilexpress_woo_oficial[api_key_generacion_ot_enabled]' value='1' <?php if($options['api_key_generacion_ot_enabled'] == '1') echo 'checked="checked"'; ?>> 
			Habilitar
			<br /><small>Necesitas este módulo para poder obtener generar Ordenes de Transporte e Imprimir tus etiquetas, crea tu API KEY <a href="https://developers.wschilexpress.com/products/transportorders/subscribe" target="_blank">aquí</a>.</small>
		</label>
	    <?php
	}

	public function chilexpress_woo_oficial_field_4_render() {
		$options = get_option( 'chilexpress_woo_oficial' );
		if (isset($options['api_key_generacion_ot_value']) && !empty($options['api_key_generacion_ot_value']) && trim($options['api_key_generacion_ot_value']) != "" ) {
			$value = $options['api_key_generacion_ot_value'];
		} else {
			$value = "0112f48125034f8fa42aef2441773793";
		}

	    ?>
	    <input type='text' name='chilexpress_woo_oficial[api_key_generacion_ot_value]' value='<? echo $value; ?>' class="regular-text"> 
	    <br /><small>Puedes encontrar esta Api Key, bajo el producto Envíos en tu página de <a href="https://developers.wschilexpress.com/developer" target="_blank">perfil</a>.</small>
	    <?php
	}


	public function chilexpress_woo_oficial_field_5_render() {
		$options = get_option( 'chilexpress_woo_oficial' );
	    ?>
	    <label for="chilexpress_woo_oficial[api_key_cotizador_enabled]">
	    	<input type='checkbox' id="generacion_ot" name='chilexpress_woo_oficial[api_key_cotizador_enabled]' value='1' <?php if($options['api_key_cotizador_enabled'] == '1') echo 'checked="checked"'; ?>> 
			Habilitar
			<br /><small>Necesitas este módulo para poder obtener calcular los gastos de envío de forma automática, crea tu API KEY <a href="https://developers.wschilexpress.com/products/rating/subscribe" target="_blank">aquí</a>.</small>
		</label>
	    <?php
	}

	public function chilexpress_woo_oficial_field_6_render() {
		$options = get_option( 'chilexpress_woo_oficial' );
		if (isset($options['api_key_cotizador_value']) && !empty($options['api_key_cotizador_value']) && trim($options['api_key_cotizador_value']) != "" ) {
			$value = $options['api_key_cotizador_value'];
		} else {
			$value = "fd46aa18a9fe44c6b49626692605a2e8";
		}
	    ?>
	    <input type='text' name='chilexpress_woo_oficial[api_key_cotizador_value]' value='<? echo $value; ?>' class="regular-text"> 
	    <br /><small>Puedes encontrar esta Api Key, bajo el producto Cotizador en tu página de <a href="https://developers.wschilexpress.com/developer" target="_blank">perfil</a>.</small>
	    <?php
	}

	public function chilexpress_woo_oficial_field_7_render() {
		$options = get_option( 'chilexpress_woo_oficial' );
	    ?>
	    <select name='chilexpress_woo_oficial[ambiente]'>
	    	<option value="staging" <?php if($options['ambiente'] == 'staging') echo 'selected="selected"'; ?>>Staging</option>
	    	<option value="production" <?php if($options['ambiente'] == 'production') echo 'selected="selected"'; ?>>Production</option>
	    </select>
	    <br /><small>Elige el ambiente de Staging para hacer las pruebas con tu plugin, y el ambiente de production una vez estas seguro(a) que todo funciona correctamente.</small>
	    <?php
	}

	/* Datos generales origen */

	public function region_origen_render() {
		$regiones = $this->coverage_data->obtener_regiones();
		$options = get_option( 'chilexpress_woo_oficial_general' );
		?>
		 <select name='chilexpress_woo_oficial_general[region_origen]' class="regular-text wc-enhanced-select select-county" data-city="comuna_origen">
		 	<?php foreach ($regiones as $key => $value) {?>
	    	<option value="<?php echo $key; ?>" <?php if(isset($options['region_origen']) &&  $options['region_origen'] == $key) echo 'selected="selected"'; ?>><?php echo $value; ?></option>
	    <?php } ?>
	    </select>
		<?php
	}
	public function comuna_origen_render() {
		$regiones = $this->coverage_data->obtener_regiones();
		$options = get_option( 'chilexpress_woo_oficial_general' );

		if (isset($options['region_origen'])) {
			$region = $options['region_origen'];
		} else {
			$region = "R1";
		}		

		$comunas = $this->coverage_data->obtener_comunas($region);
		$comuna_val = reset($comunas); // First element's value
		$comuna_id = key($comunas); // First element's key

		?>
		 <input type="text" disabled="true"  value="<?php if(isset($options['comuna_origen'])){ echo $options['comuna_origen']; } else { echo $comuna_id; }?>" style="width:6em;" />
		
		 <select name="chilexpress_woo_oficial_general[comuna_origen]" id="comuna_origen" class="regular-text wc-enhanced-select select-city">
	    <?php foreach ($comunas as $key => $value) {?>
	    	<option value="<?php echo $key; ?>" <?php if(isset($options['comuna_origen']) &&  $options['comuna_origen'] == $key) echo 'selected="selected"'; ?>><?php echo $value; ?></option>
	    <?php } ?>
	    </select>
		<?php
	}
	public function numero_tcc_origen_render() {
		$options = get_option( 'chilexpress_woo_oficial_general' );
		?>
		 <input type="text" name='chilexpress_woo_oficial_general[numero_tcc_origen]' value="<? if(isset($options['numero_tcc_origen'])) echo $options['numero_tcc_origen'];?>" class="regular-text"/>
		<?php
	}

	/* Datos generales remitente */
	
	public function nombre_remitente_render() {
		$options = get_option( 'chilexpress_woo_oficial_general' );
		?>
		 <input type="text" name='chilexpress_woo_oficial_general[nombre_remitente]' value="<? if(isset($options['nombre_remitente'])) echo $options['nombre_remitente'];?>" class="regular-text"/>
		<?php
	}
	public function telefono_remitente_render() {
		$options = get_option( 'chilexpress_woo_oficial_general' );
		?>
		 <input type="text" name='chilexpress_woo_oficial_general[telefono_remitente]' value="<? if(isset($options['telefono_remitente'])) echo $options['telefono_remitente'];?>" class="regular-text"/>
		<?php
	}
	public function email_remitente_render() {
		$options = get_option( 'chilexpress_woo_oficial_general' );
		?>
		 <input type="text" name='chilexpress_woo_oficial_general[email_remitente]' value="<? if(isset($options['email_remitente'])) echo $options['email_remitente'];?>" class="regular-text"/>
		<?php
	}
	public function rut_seller_remitente_render() {
		$options = get_option( 'chilexpress_woo_oficial_general' );
		?>
		 <input type="text" name='chilexpress_woo_oficial_general[rut_seller_remitente]' value="<? if(isset($options['rut_seller_remitente'])) echo $options['rut_seller_remitente'];?>" class="regular-text"/>
		<?php
	}

	public function rut_marketplace_remitente_render() {
		$options = get_option( 'chilexpress_woo_oficial_general' );
		?>
		 <input type="text" name='chilexpress_woo_oficial_general[rut_marketplace_remitente]' value="<? if(isset($options['rut_marketplace_remitente'])) echo $options['rut_marketplace_remitente'];?>" class="regular-text"/>
		<?php
	}

	/* Datos generales devolucion */
	public function region_devolucion_render() {
		$regiones = $this->coverage_data->obtener_regiones();
		$options = get_option( 'chilexpress_woo_oficial_general' );
		?>
		 <select name='chilexpress_woo_oficial_general[region_devolucion]' class="regular-text wc-enhanced-select select-county" data-city="comuna_devolucion">
		 	<?php foreach ($regiones as $key => $value) {?>
	    	<option value="<?php echo $key; ?>" <?php if(isset($options['region_devolucion']) &&  $options['region_devolucion'] == $key) echo 'selected="selected"'; ?>><?php echo $value; ?></option>
	    <?php } ?>
	    </select>
		<?php
	}
	public function comuna_devolucion_render() {		
		$regiones = $this->coverage_data->obtener_regiones();
		$options = get_option( 'chilexpress_woo_oficial_general' );

		if (isset($options['region_devolucion'])) {
			$region = $options['region_devolucion'];
		} else {
			$region = "R1";
		}		

		$comunas = $this->coverage_data->obtener_comunas($region);
		$comuna_val = reset($comunas); // First element's value
		$comuna_id = key($comunas); // First element's key

		?>
		 <input type="text" disabled="true"  value="<?php if(isset($options['comuna_devolucion'])){ echo $options['comuna_devolucion']; } else { echo $comuna_id; }?>" style="width:6em;"/>
		
		 <select name="chilexpress_woo_oficial_general[comuna_devolucion]" id="comuna_devolucion" class="regular-text wc-enhanced-select select-city">
	    <?php foreach ($comunas as $key => $value) {?>
	    	<option value="<?php echo $key; ?>" <?php if(isset($options['comuna_devolucion']) &&  $options['comuna_devolucion'] == $key) echo 'selected="selected"'; ?>><?php echo $value; ?></option>
	    <?php } ?>
	    </select>
		<?php
	}

	public function calle_devolucion_render() {
		$options = get_option( 'chilexpress_woo_oficial_general' );
		?>
		 <input type="text" name='chilexpress_woo_oficial_general[calle_devolucion]' value="<? if(isset($options['calle_devolucion']))  echo $options['calle_devolucion'];?>" class="regular-text"/>
		<?php
	}
	public function numero_calle_devolucion_render() {
		$options = get_option( 'chilexpress_woo_oficial_general' );
		?>
		 <input type="text" name='chilexpress_woo_oficial_general[numero_calle_devolucion]' value="<? if(isset($options['numero_calle_devolucion']))  echo $options['numero_calle_devolucion'];?>" class="regular-text"/>
		<?php
	}

	public function complemento_devolucion_render() {
		$options = get_option( 'chilexpress_woo_oficial_general' );
		?>
		 <input type="text" name='chilexpress_woo_oficial_general[complemento_devolucion]' value="<? if(isset($options['complemento_devolucion']))  echo $options['complemento_devolucion'];?>" class="regular-text"/>
		<?php
	}
	
 
	/**
	 * Register the stylesheets for the admin area.
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
		wp_enqueue_style('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/chilexpress-woo-oficial-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Set the style for the tracking order menus
	 *
	 * @since    1.0.0
	 */
	public function add_custom_order_status_actions_button_css() {
	    $action_slug = "generar_ot"; // The key slug defined for your action button
	    $action_slug2 = "imprimir_ot"; // The key slug defined for your action button

	    echo '<style>.wc-action-button-'.$action_slug.'::after  { font-size:1.4em; font-family: dashicons !important; content: "\f111" !important; margin-top:-1px !important;}</style>';
	    echo '<style>.wc-action-button-'.$action_slug2.'::after { font-size:1.4em; font-family: dashicons !important; content: "\f457" !important; margin-top:-1px !important; }</style>';

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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


		// We need select2 to show fancy selects with search capabilities
		wp_enqueue_script('select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array('jquery') );
		// We need the the plugin admin js
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/chilexpress-woo-oficial-admin.js', array( 'jquery' ), $this->version, false );
		// we use this ajax call to show get global  ars and the right nonce that we need
		wp_localize_script( $this->plugin_name, 'ajax_var', array(
	        'url'    => admin_url( 'admin-ajax.php' ),
	        'nonce'  => wp_create_nonce( 'cwo-ajax-nonce' ),
	        'action' => 'event-list'
    	) );
		// we need to show a modal for edit.php?post_type=shop_order

		if ( is_dir( WP_PLUGIN_DIR . '/woocommerce-3.9.2' ) ) {
			$backbone_path = '/wp-content/plugins/woocommerce-3.9.2/assets/js/admin/backbone-modal.js';
		}
		elseif( is_dir( WP_PLUGIN_DIR . '/woocommerce') )
		{
			$backbone_path = '/wp-content/plugins/woocommerce/assets/js/admin/backbone-modal.js';
		}

    	wp_enqueue_script(
		   'backbone-modal',
		   get_site_url() . $backbone_path,
		   array('jquery', 'wp-util', 'backbone')
		);


	}

	public function add_menus() {
		add_menu_page ( 'Chilexpress', 'Chilexpress', 'manage_options', 'chilexpress_woo_oficial_menu', array($this, 'chilexpress'), 'dashicons-admin-generic' );
 
    
		add_submenu_page ( 'chilexpress_woo_oficial_menu', 'Habilitación de Módulos', 'Habilitación de Módulos', 'manage_options', 'chilexpress_woo_oficial_menu', array($this, 'habilitar_modulos') );
	 
		add_submenu_page ( 'chilexpress_woo_oficial_menu', 'Configuración General', 'Configuración General', 'manage_options', 'chilexpress_woo_oficial_submenu2', array($this, 'configuracion_general') );

		add_submenu_page ( 'chilexpress_woo_oficial_menu', 'Generador de OT', 'Generador de OT', 'manage_options', 'chilexpress_woo_oficial_generar_ot', array($this, 'generar_ot') );
	}
	
	public function chilexpress() {

	}

	public function generar_ot() {
		$options = get_option( 'chilexpress_woo_oficial_general' );
		if (isset($_GET['_wpnonce'])) {
			$nonce = $_GET['_wpnonce'];
			if ( ! wp_verify_nonce( $nonce, 'generar-ot' ) ) {
			     die( 'Invalid Nonce' ); 
			}
		} else {
			die( 'Missing Nonce' ); 
		}

		$action = isset($_GET['action'])?$_GET['action']:'generar_ot';
		$order_id = intval($_GET['order_id']);
		if (!$order_id && $order_id < 0) {
			die("Invalid Order Id");
		}
		$order = wc_get_order( $order_id );
		
		if (!$order) {
			die("Invalid Order");	
		}

		$metodos = array (
			3 => 'Chilexpress - DIA HABIL SIGUIENTE',
			4 => 'Chilexpress - DIA HABIL SUBSIGUIENTE',
			5 => 'Chilexpress - TERCER DIA HABIL'
		);
		if ($action == 'generar_ot') {
			$order_data = $order->get_data();

			$serviceTypeId = 0;
			foreach ( $order_data['shipping_lines'] as $key => $obj ) {
				foreach ($metodos as $method_id => $method_name)
				{
					if ($obj->get_method_title() == $method_name) {
						$serviceTypeId = $method_id;
					}
				}
			}
			//echo '<pre>'.print_r($order_data, true).'</pre>';
			//echo '<pre> '.print_r(get_post_meta($order_id,'shipping_address_3'), true).'</pre>';
			$complemento = get_post_meta($order_id,'shipping_address_3', true)?get_post_meta($order_id,'shipping_address_3', true):get_post_meta($order_id,'billing_address_3', true);
			//echo '<pre>'.print_r($complemento, true).'</pre>';
			if (isset($_POST['subaction']) && $_POST['subaction'] == 'generar') {

				$payload_header = array(
					    "certificateNumber" => 0, //Número de certificado, si no se ingresa se creará uno nuevo
					    "customerCardNumber"=> $options["numero_tcc_origen"], // Número de Tarjeta Cliente Chilexpress (TCC)
					    "countyOfOriginCoverageCode"=> $options["comuna_origen"], // Comuna de origen
					    "labelType"=> 2, // Imagen
					    "marketplaceRut"=> intval($options["rut_marketplace_remitente"]), // Rut asociado al Marketplace
					    "sellerRut"=> "DEFAULT" // Rut asociado al Vendedor
		  			);
				$payload_address_destino = array(
			  						"addressId" => 0, // Id de la dirección obtenida de la API Validar dirección
							        "countyCoverageCode"=>  $order_data["shipping"]["city"], // Cobertura de destino obtenido por la API Consultar Coberturas
							        "streetName"=> $order_data["shipping"]["address_1"], // Nombre de la calle
							        "streetNumber"=> $order_data["shipping"]["address_2"], // Numeración de la calle
							        "supplement"=> $order_data["customer_note"], // Información complementaria de la dirección
							        "addressType"=> "DEST", // Tipo de dirección; DEST = Entrega, DEV = Devolución.
							        "deliveryOnCommercialOffice"=> false, // Indicador si es una entrega en oficina comercial (true) o entrega en domicilio (false)
							        "commercialOfficeId"=> "",
							        "observation"=> "DEFAULT" // Observaciones adicionales
		  						);
				$payload_address_devolucion = array(
									"addressId"=> 0,
									"countyCoverageCode"=> $options['comuna_devolucion'],
									"streetName"=> $options['calle_devolucion'],
									"streetNumber"=> $options['numero_calle_devolucion'],
									"supplement"=> $options['complemento_devolucion'],
									"addressType"=> "DEV",
									"deliveryOnCommercialOffice"=> false,
									"observation"=> "DEFAULT"
		  						);
				$payload_contact_devolucion = array(
									"name"=> $options['nombre_remitente'],
									"phoneNumber"=> $options['telefono_remitente'],
									"mail"=> $options['email_remitente'],
									"contactType"=> "R" // Tipo de contacto; Destinatario (D), Remitente (R)
		  						);
				$payload_contact_destino = array(
									"name"=> $order_data["shipping"]["first_name"]." ".$order_data["shipping"]["last_name"],
									"phoneNumber"=> $order_data["billing"]["phone"],
									"mail"=> $order_data["billing"]["email"],
									"contactType"=> "D" // Tipo de contacto; Destinatario (D), Remitente (R)
		  						);

				$pre_paquetes = array();
				$paquetes = array();
				$opcion_paquetes = $_POST["paquetes"];

				foreach($opcion_paquetes as $prodid => $numero_paquete ):

					foreach ($order->get_items() as $item_key => $item ):
					    $item_id = $item->get_id();
					    $product      = $item->get_product(); // Get the WC_Product object
					    $product_id   = $item->get_product_id(); // the Product id
					    $quantity     = $item->get_quantity();					
					    if ("$prodid" == "$product_id") {
							if (isset($pre_paquetes[$numero_paquete])) {
								$pre_paquetes[$numero_paquete]["weight"] += $product->get_weight()*$quantity;
								$pre_paquetes[$numero_paquete]["total"] += $product->get_price()*$quantity;
								$pre_paquetes[$numero_paquete]["volumes"]["$item_id"] = $product->get_dimensions(false)['height']*$quantity*$product->get_dimensions(false)['width']*$product->get_dimensions(false)['length'];
							} else {
								$pre_paquetes[$numero_paquete] = array(
									"weight"=> $product->get_weight()*$quantity,
									"total"=> $product->get_price()*$quantity,
									"volumes" => array(
										"$item_id" =>  $product->get_dimensions(false)['height']*$quantity*$product->get_dimensions(false)['width']*$product->get_dimensions(false)['length']

									)
								);
							}
						}
					endforeach;
				endforeach;

				foreach($pre_paquetes as $numero_paquete => $base_paquete ):
					// ordenamos los volumenes en volumen de mayor a menor
					arsort($base_paquete["volumes"]);
					// obtenemos el id del producto 
					$biggest_product_id = array_key_first($base_paquete["volumes"]);
					foreach ($order->get_items() as $item_key => $item ):
					    $item_id = $item->get_id();
					    $product      = $item->get_product(); // Get the WC_Product object
					    $product_id   = $item->get_product_id(); // the Product id
					    $quantity     = $item->get_quantity();

					    if ($item_id == $biggest_product_id) {
					    	$paquetes[] =  array(
									"weight"=> $base_paquete["weight"], // Peso en kilogramos
									"height"=> $product->get_dimensions(false)['height']*$quantity, // Altura en centímetros
									"width"=> $product->get_dimensions(false)['width'], // Ancho en centímetros
									"length"=> $product->get_dimensions(false)['length'],  // Largo en centímetros
									"serviceDeliveryCode"=> $serviceTypeId, // Código del servicio de entrega, obtenido de la API Cotización
									"productCode"=> "3", // Código del tipo de roducto a enviar; 1 = Documento, 3 = Encomienda
									"deliveryReference"=> "ORDEN-".$order_id, // Referencia que permite identificar el envío por parte del cliente.
									"groupReference"=> "ORDEN-".$order_id."-GRUPO-1", // Referencia que permite identificar un grupo de bultos que va por parte del cliente.
									"declaredValue"=> $base_paquete["total"], // Valor declarado del producto
									"declaredContent"=> "string", // Tipo de producto enviado; 1 = Moda, 2 = Tecnologia, 3 = Repuestos, 4 = Productos medicos, 5 = Otros
									"extendedCoverageAreaIndicator"=> false, // Indicador de contratación de cobertura extendida 0 = No, 1 = Si
									"receivableAmountInDelivery"=> 1000 // Monto a cobrar, en caso que el cliente tenga habilitada esta opción.
		  						);
					    }
					endforeach;
				endforeach;				


				$payload = array(
					"header" => $payload_header,
		  			"details" => array(
		  				array(
		  					"addresses" => array(
		  						$payload_address_destino,
		  						$payload_address_devolucion
		  					),
		  					"contacts" => array( // Se debe entregar un detalle para los datos de contacto del destinatario (D) y otro para los del remitente (R)
		  						$payload_contact_devolucion,
		  						$payload_contact_destino
		  					),
		  					"packages" => $paquetes

		  				)
		  			)
				);

				$api = new Chilexpress_Woo_Oficial_API();
				$result = $api->generar_ot($payload);


				if ( is_wp_error( $result ) ) {
				   $error_message = $result->get_error_message();
				   echo "Something went wrong: $error_message";
				   die();
				} else {
			   		$json_response = $result;
					   
		   			$statusCode = $json_response->statusCode;
		   			$statusDescriptions = array();

					if($statusCode != 99)
					{
						$countOfGeneratedOrders = $json_response->data->header->countOfGeneratedOrders;
						$certificateNumber = $json_response->data->header->certificateNumber;
					}
					else
					{
						$countOfGeneratedOrders = 0;
					}

		   			if ($statusCode == 99) {

						$countOfGeneratedOrders = count($json_response->data->detail);

						for($i = 0; $i < $countOfGeneratedOrders; $i++ )
						{
			   				$statusDescriptions[] =  $json_response->data->detail[$i]->statusDescription;
			   			}
		   				?>

		   				<div id="message2" class="notice notice-error"><p>Hubo un error al llamar a la API de Chilexpress <strong><?php echo esc_html(implode(", ", $statusDescriptions));?></strong>. </p><p>La orden de transporte no fue generada, por favor intentelo mas tarde.</p></div>

		   				<?php
		   				die();
					}

					if($countOfGeneratedOrders == 0)
					{
		   				?>

		   				<div id="message2" class="notice notice-error"><p>Hubo un error al llamar a la API de Chilexpress <strong>No hay ordenes generadas</strong>. </p><p>La orden de transporte no fue generada, por favor intentelo mas tarde.</p></div>

		   				<?php
		   				die();
					}
					   

		   			$transportOrderNumbers = array();
		   			$barcodes = array();
		   			$labelsData = array();
		   			$references = array();
		   			$productDescriptions = array();
		   			$serviceDescription_ = array();
					$classificationData_ = array();
					$companyName_ = array();
					$recipient_ = array();
					$address_ = array();
					$printedDate_ = array();

		   			for($i = 0; $i < $countOfGeneratedOrders; $i++ ) {
		   				$transportOrderNumbers[$i] = $json_response->data->detail[$i]->transportOrderNumber;
		   				$barcodes[$i] = $json_response->data->detail[$i]->barcode;
		   				$references[$i] = $json_response->data->detail[$i]->reference;
		   				$productDescriptions[$i] = $json_response->data->detail[$i]->productDescription;
			   			$serviceDescription_[$i] = $json_response->data->detail[$i]->serviceDescription;
						$classificationData_[$i] = $json_response->data->detail[$i]->classificationData;
						$companyName_[$i] = $json_response->data->detail[$i]->companyName;
						$recipient_[$i] = $json_response->data->detail[$i]->recipient;
						$address_[$i] = $json_response->data->detail[$i]->address;
						$printedDate_[$i] = $json_response->data->detail[$i]->printedDate;
		   				$labelsData[$i] = $json_response->data->detail[$i]->label->labelData;
		   			}

		   			$order->update_meta_data( 'transportOrderNumbers', $transportOrderNumbers );
		   			$order->update_meta_data( 'barcodes', $barcodes );
		   			$order->update_meta_data( 'references', $references );
		   			$order->update_meta_data( 'productDescriptions', $productDescriptions );
		   			$order->update_meta_data( 'serviceDescription_', $serviceDescription_ );
		   			$order->update_meta_data( 'classificationData_', $classificationData_ );
		   			$order->update_meta_data( 'companyName_', $companyName_ );
		   			$order->update_meta_data( 'recipient_', $recipient_ );
		   			$order->update_meta_data( 'address_', $address_ );
		   			$order->update_meta_data( 'printedDate_', $printedDate_ );
		   			$order->update_meta_data( 'labelsData', $labelsData );
		   			$order->update_meta_data( 'ot_status', 'created' );
    				$order->save();
			   	
			   		?>
			   			<p>Redireccionando...</p>
			   			<script type="text/javascript">document.location = '<?php echo admin_url('edit.php?post_type=shop_order'); ?>';</script>
			   		<?php
			   		die();
				 
				 
				}
			}
			$continuar_orden = true;
			if (!isset($options["numero_tcc_origen"]) ||($options["numero_tcc_origen"]) == ""){
				$continuar_orden = false;
				?>
				<div id="message" class="notice notice-error"><p>Debe ingresar un <strong>Número TCC</strong> en la configuración general de Chilexpress para <strong>Generar una OT</strong>.</p></div>
				<?php
			}
			if (!isset($options["comuna_origen"]) || ($options["comuna_origen"]) == "" ){
				$continuar_orden = false;
				?>
				<div id="message2" class="notice notice-error"><p>Debe seleccionar una <strong>Comuna de Origen</strong> en la configuración general de Chilexpress para <strong>Generar una OT</strong>.</p></div>
				<?php
			}
			if (!isset($options["rut_marketplace_remitente"]) ||($options["rut_marketplace_remitente"]) == ""){
				$continuar_orden = false;
				?>
				<div id="message2" class="notice notice-error"><p>Debe seleccionar un <strong>Rut Marketplace</strong> en la configuración general de Chilexpress para <strong>Generar una OT</strong>.</p></div>
				<?php
			}


			require plugin_dir_path( __FILE__ ) . 'partials/chilexpress-woo-oficial-admin-form-ot.php';

		} else if($action == 'imprimir_ot') {
			$transportOrderNumbers = $order->get_meta( 'transportOrderNumbers');
			$labelsData = $order->get_meta( 'labelsData');
			$transportOrderNumbers = $order->get_meta( 'transportOrderNumbers');
   			$barcodes = $order->get_meta( 'barcodes');
   			$references = $order->get_meta( 'references');
   			$productDescriptions = $order->get_meta( 'productDescriptions');
   			$serviceDescription_ = $order->get_meta( 'serviceDescription_');
   			$classificationData_ = $order->get_meta( 'classificationData_');
   			$companyName_ = $order->get_meta( 'companyName_');
   			$recipient_ = $order->get_meta( 'recipient_');
   			$address_ = $order->get_meta( 'address_' );
   			$printedDate_ = $order->get_meta( 'printedDate_');
   			$labelsData = $order->get_meta( 'labelsData');
			
			?>
			<h2>Imprimir OT</h2>
			<h3>Etiquetas</h3>
			<?php 
	
			if (is_array($transportOrderNumbers)) {
				$out = array();
				for($i = 0; $i <count($transportOrderNumbers); $i++) {
					$encoded = $labelsData[$i];
					$print_url = plugin_dir_url( __DIR__ ).'print-label.php?order_id='.$order_id;
					$src = 'data:image/jpg;base64,'.$encoded;
					require plugin_dir_path( __FILE__ ) . 'partials/chilexpress-woo-oficial-admin-labels.php';
				}
			}
			?>

			<?php
		}
	}

	public function habilitar_modulos() {
		$countries_obj   = new WC_Countries();
    	$shipping_countries = $countries_obj->get_shipping_countries( );
    	if(!array_key_exists("CL", $shipping_countries) || count($shipping_countries) > 1){
    		?>
    		 <div id="message" class="notice notice-error"><p>El Plugin de Chilexpress solo funciona para enviós en Chile, se recomienda deshabilitar el envio a otros paises <a href="<?php echo admin_url().'admin.php?page=wc-settings'?>">aquí</a> en la sección <strong>Opciones Generales</strong>.</p></div>
    		<?php
    	}
	?>
    <form action='options.php' method='post' class="chilexpress-modules-form">
        <h2>Habilitar módulos</h2>
        <?php  if (isset($_GET['settings-updated'])): ?>
            <div id="message" class="updated notice is-dismissible"><p>La configuración de módulos fue actualizada con éxito.</p></div>
        <?php endif; ?>
        <p style="margin-bottom: -3em;">Para poder trabajar de forma adecuada necesitas crear tus Api Keys en el siguiente URL
        	<a href="https://developers.wschilexpress.com/products" target="_blank">https://developers.wschilexpress.com/products</a>
        </p>
        <?php
        settings_fields( 'chilexpress-woo-oficial' ); 
        do_settings_sections( 'chilexpress-woo-oficial' );
        submit_button("Guardar");
        ?>
    </form>
    <?php
	}

	public function configuracion_general() {
		$countries_obj   = new WC_Countries();
    	$shipping_countries = $countries_obj->get_shipping_countries( );
    	if(!array_key_exists("CL", $shipping_countries) || count($shipping_countries) > 1){
    		?>
    		 <div id="message" class="notice notice-error"><p>El Plugin de Chilexpress solo funciona para enviós en Chile, se recomienda deshabilitar el envio a otros paises <a href="<?php echo admin_url().'admin.php?page=wc-settings'?>">aquí</a> en la sección <strong>Opciones Generales</strong>.</p></div>
    		<?php
    	}
	?>
    <form action='options.php' method='post' class="solve-city-county">
    	<h1>Opciones Generales</h1>
    	<?php  if (isset($_GET['settings-updated'])): ?>
            <div id="message" class="updated notice is-dismissible"><p>Las opciones generales de Chilexpress fueron actualizadas con éxito.</p></div>
        <?php endif; ?>
        <?php
        settings_fields( 'chilexpress-woo-oficial-general' );
        do_settings_sections( 'chilexpress-woo-oficial-general' );
        submit_button("Guardar");
        ?>
    </form>
    <?php
	}

	public function obtener_regiones_handle_ajax_request() {
		
		$response	= array();
		$response['message'] = "Successfull Request";
		$regiones = $this->coverage_data->obtener_regiones();
		$response['regiones'] = $regiones;

    	echo json_encode($response);
    	exit;
	}

	public function obtener_comunas_desde_region_handle_ajax_request() {
		$region	= isset($_POST['region'])?trim($_POST['region']):"";
		$response	= array();
		$response['message'] = "Successfull Request";
		$comunas = $this->coverage_data->obtener_comunas($region);
		$response['comunas'] = $comunas;

    	echo json_encode($response);
    	exit;
	}

	public function track_order_handle_ajax_request() {
		$ot	= isset($_POST['ot'])?trim($_POST['ot']):"";
		$pid = isset($_POST['pid'])?trim($_POST['pid']):1;
		$options = get_option( 'chilexpress_woo_oficial_general' );

		$order = wc_get_order( $pid );
		$transportOrderNumbers = $order->get_meta( 'transportOrderNumbers');
		if (!in_array($ot, $transportOrderNumbers )) {
			echo json_encode(array('ot'=>$ot,'error'=> "Numero de Orden Invalida",'response' => json_decode($response['body']) ));
			exit;
		}

		$api = new Chilexpress_Woo_Oficial_API();
		$result = $api->obtener_estado_ot($ot, "ORDEN-".$pid, intval($options["rut_marketplace_remitente"])); 

		if ( is_wp_error( $result ) ) {
		   $error_message = $result->get_error_message();
		   echo json_encode(array('ot'=>$ot,'error'=> $error_message ));
		} else {
		   echo json_encode(array('ot'=>$ot,'response' => $result));
		}
		
		exit;
	}



}


if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
}
