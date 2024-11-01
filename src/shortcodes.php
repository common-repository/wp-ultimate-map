<?php
if(!defined('ABSPATH'))
	exit();
if(!class_exists('wpgl_shortcode')) :

class wpgl_shortcode {
	
	public function __construct() {
		add_shortcode( 'umap', array($this , 'render_shortcode') );
		add_action( 'wp_enqueue_scripts', array($this , 'custom_shortcode_scripts'));
		
	}
	
	function render_shortcode( $atts=array() ) {
		
		$focus_point = get_option('focus_point');
		if($focus_point == false )  {
			$focus_point['lat'] = '34.00';
			$focus_point['lng'] = '76.00';
		}
		$zoom_level = get_option('wpgl_zoom_level');
		if($zoom_level == false) {
			$zoom_level = 4;
		}
		$select_all_places = get_option('wpgl_sel_places');
		$select_all_routes = get_option('wpgl_sel_routes');
		$atts = shortcode_atts( array(
				'focus_lat' => $focus_point['lat'] ,
				'focus_lng' => $focus_point['lng'] ,
				'zoom_level' => $zoom_level,
				'markers'	=> '' ,
				'routes'	=> ''
		), $atts, 'umap' );

		if($atts['markers'] != '') 		
			$markers_terms = explode(',' , $atts['markers']);

		if($atts['routes'] != '')
			$routes_terms = explode(',' , $atts['routes']);
			ob_start();
			include_once WP_GEO_PATH.'/view/view.php';
			$output = ob_get_contents();
			ob_end_clean();
			wp_enqueue_script( 'google_map_script' , 'https://maps.googleapis.com/maps/api/js?libraries=places&callback=mapLocation');
		return $output;
	}
	
	function custom_shortcode_scripts() {
		global $post;
		if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'umap') ) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('googe_map_scripts-js' , WP_GEO_URL.'/js/scripts.js');
			wp_enqueue_style('google_map-css' , WP_GEO_URL.'/css/main.css');
		}
	}
	

}

endif;
new wpgl_shortcode();