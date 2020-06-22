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

		wp_localize_script( $this->plugin_name, 'woocommerce_chilexpress', array(
	        'base_url'    => plugin_dir_url( __FILE__ ),
	        'nonce'  => wp_create_nonce( 'cwo-clxp-ajax-nonce' )
    	) );

	}

}
