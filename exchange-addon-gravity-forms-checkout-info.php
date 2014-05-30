<?php
/*
Plugin Name: iThemes Exchange – Gravity Forms Checkout Info Add-on
Plugin URI: http://www.ironbounddesigns.com
Description: Add a Gravity Form for customers to fill out during the checkout process
Version: 1.0
Author: Iron Bound Designs
Author URI: http://www.ironbounddesigns.com
License: GPL2
*/

/**
 * This registers our plugin as a product feature add-on
 *
 * @since 1.0.0
 *
 * @return void
 */
function it_exchange_register_gravity_forms_checkout_form() {
	$options = array(
	  'name'        => __( 'Gravity Forms Checkout Form', 'ibd_gravity_forms_checkout_info' ),
	  'description' => __( 'Add a Gravity Form for customers to fill out during the checkout process', 'ibd_gravity_forms_checkout_info' ),
	  'author'      => 'Iron Bound Designs',
	  'author_url'  => 'http://www.ironbounddesigns.com',
	  'file'        => dirname( __FILE__ ) . '/init.php',
	  'category'    => 'product-feature',
	  'basename'    => plugin_basename( __FILE__ ),
	  'labels'      => array(
		'singular_name' => __( 'Gravity Forms Checkout Form', 'ibd_gravity_forms_checkout_info' ),
	  )
	);
	it_exchange_register_addon( 'ibd-gravity-forms-info-product-feature', $options );
}

add_action( 'it_exchange_register_addons', 'it_exchange_register_gravity_forms_checkout_form' );

class IBD_GFCI_Plugin {
	/**
	 *
	 */
	const SLUG = 'ibd_gravity_forms_checkout_info';

	/**
	 * @var string
	 */
	static $dir;

	/**
	 * @var string
	 */
	static $url;

	/**
	 *
	 */
	public function __construct() {
		self::$dir = plugin_dir_path( __FILE__ );
		self::$url = plugin_dir_url( __FILE__ );
		spl_autoload_register( array( "IBD_GFCI_Plugin", "autoload" ) );
	}

	/**
	 * Autoloader
	 *
	 * @param $class_name string
	 */
	public static function autoload( $class_name ) {
		if ( substr( $class_name, 0, 8 ) != "IBD_GFCI" ) {
			$path = self::$dir . "lib/classes";
			$class = strtolower( $class_name );

			$name = str_replace( "_", "-", $class );
		}
		else {
			$path = self::$dir . "lib";

			$class = substr( $class_name, 8 );
			$class = strtolower( $class );

			$parts = explode( "_", $class );
			$name = array_pop( $parts );

			$path .= implode( "/", $parts );
		}

		$path .= "/class.$name.php";

		if ( file_exists( $path ) ) {
			require( $path );

			return;
		}

		if ( file_exists( str_replace( "class.", "abstract.", $path ) ) ) {
			require( str_replace( "class.", "abstract.", $path ) );

			return;
		}

		if ( file_exists( str_replace( "class.", "interface.", $path ) ) ) {
			require( str_replace( "class.", "interface.", $path ) );

			return;
		}
	}
}

new IBD_GFCI_Plugin();