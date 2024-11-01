<?php
/*
Plugin Name: WP-Ultimate-Map
Plugin URI: 
Description: 
Author: WebsEfficient
Version: 1.1
Author URI: 
Text Domain: wp-geo-location
Domain Path: /language
*/

define('WP_GEO_PATH', WP_PLUGIN_DIR.'/wp-ultimate-map');
define('WP_GEO_URL', WP_PLUGIN_URL.'/wp-ultimate-map');
define('TEXT_DOMAIN', 'wp-ultimate-map');

class WPGeoLocation{
	protected static
	$instance = null;

	final protected function __construct(){

	}

	public static function app(){
		if(!(self::$instance instanceof self)){

			self::$instance = new self();
			load_plugin_textdomain(TEXT_DOMAIN, false, WP_GEO_PATH.'/languages');
			include_once(WP_GEO_PATH.'/src/CPT.php');
			include_once(WP_GEO_PATH.'/src/register-post-types.php');
			include_once(WP_GEO_PATH.'/src/ajax.php');
			if(is_admin()) {
				include_once(WP_GEO_PATH.'/admin/class-admin.php');
			}else {
				
				include_once (WP_GEO_PATH.'/src/shortcodes.php');
			}
		}

		return self::$instance;
	}
}

function WPGeoLocation(){
	static $app;
	if(!($app instanceof WPGeoLocation))
		$app = WPGeoLocation::app();
	return $app;
}
WPGeoLocation();


//add_action( 'plugins_loaded', 'wp_geo_load_textdomain' );
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function wp_geo_load_textdomain() {
  load_plugin_textdomain( 'wp-geo-location', false, WP_GEO_PATH . '/language' ); 
}
?>
