<?php
if(!defined('ABSPATH')) 
	exit();
if(!class_exists('WPGeoLocation_admin')) :

class WPGeoLocation_admin {
	
	public function __construct() {
		include_once(WP_GEO_PATH.'/admin/post-types/place-marker.php');
		include_once(WP_GEO_PATH.'/admin/post-types/place-route.php');
		
		add_action('admin_enqueue_scripts' , array($this , 'enqueue_scripts')); 
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init' , array($this , 'process_init'));
	}
	
	public static function load_google_map_js() {
		echo '<script src="https://maps.googleapis.com/maps/api/js?libraries=places&callback=initMap" async defer></script>';
	}
	
	public function process_init() {
		if(isset($_POST['save-setting'])) {
			$focus_point = array('lat' => $_POST['focus-lat'] , 'lng' => $_POST['focus-lng']);
			$zoom_level = $_POST['zoom-level'];
			update_option('wpgl_zoom_level', $zoom_level);
			update_option('focus_point', $focus_point);
			update_option('wpgl_sel_routes', $_POST['sel_routes']);
			update_option('wpgl_sel_places', $_POST['sel_places']);
		}
	}
	
	public function admin_menu () {
		add_options_page( 'Google Map settings','Google Map settings','manage_options','wpgl-settings', array( $this, 'settings_page' ) );
	}
	
	public function  settings_page () {
		$focus_point = get_option('focus_point', $focus_point);
		if($focus_point == false)  {
			$focus_point['lat'] = '34.00';
			$focus_point['lng'] = '76.00';
		}
		
		$zoom_level = get_option('wpgl_zoom_level');
		if($zoom_level == false) {
			$zoom_level = 4;
		}
		
		
		echo '<h1>Google Map Settings</h1>';
		?>
			<div class="setting-container">
			<form action="" method="post">
			
			<table cellspacing="0" cellpadding="0">
				<tr>
				<th><label>Set Focus Point</label></th>
				<td width="90%">
					<input id="pac-input" class="controls" type="text" placeholder="Search Box">
					<div class="map"><div id="place"></div></div></td>
				</tr>
				<tr>
					<th><label>Set Zoom Level</label></th>
					<td><input type="number" id="zoom-level" min="1" max="20" name="zoom-level" value="<?php echo $zoom_level;?>" ></td>
				</tr>
				<tr>
					<th><label>Select All Places</label></th>
					<td>
					<select name="sel_places">
						<option value="1" <?php echo get_option('wpgl_sel_places') ? 'selected="selected"' : ''; ?>>Yes</option>
						<option value="0" <?php echo !get_option('wpgl_sel_places') ? 'selected="selected"' : ''; ?>>No</option>
					</select>
					</td>
				</tr>
				<tr>
					<th><label>Select All Routes</label></th>
					<td>
					<select name="sel_routes">
						<option value="1" <?php echo get_option('wpgl_sel_routes') ? 'selected="selected"' : ''; ?>>Yes</option>
						<option value="0" <?php echo !get_option('wpgl_sel_routes') ? 'selected="selected"' : ''; ?>>No</option>
					</select>
					</td>
				</tr>
				<tr>
					<th></th>
					<td>
					<input type="hidden" name="focus-lat" id="focus-lat" value="<?php echo $focus_point['lat']; ?>" />
			<input type="hidden" name="focus-lng" id="focus-lng" value="<?php echo $focus_point['lng']; ?>" />
			
						<input type="submit" class="button button-primary" value="Save Settings" name="save-setting">
					</td>
				</tr>
			</table>
			</form>
			</div>
		<?php 
		echo '	
				
				  <script>

var map;
var markers = [];
	
function initMap() {
	var initLatLng = {lat: '.$focus_point['lat'].' , lng: '.$focus_point['lng'].'};		
	map = new google.maps.Map(document.getElementById(\'place\'), {
    center: initLatLng,
    zoom: '.$zoom_level.'
  });
		
	placeMarkerWithId(initLatLng , map , 1);
		
 map.addListener(\'click\', function(e) {
	clearMarkers();
    placeMarkerWithId(initLatLng , map , 1);
		
	jQuery(\'#focus-lat\').val(e.latLng.lat());
	jQuery(\'#focus-lng\').val(e.latLng.lng());
  });	

	place_search(map);
	jQuery(function(){
		jQuery(\'#zoom-level\').change(function(){
			map.setZoom(parseInt(jQuery(\'#zoom-level\').val()));
		});	
	});
	
}</script>
';
WPGeoLocation_admin::load_google_map_js();
}
	
	public function enqueue_scripts() {
		wp_enqueue_script('admin_enqueue-js' , WP_GEO_URL.'/admin/js/admin.js');
		wp_enqueue_style('admin-wpgl-css' , WP_GEO_URL.'/admin/css/admin.css');
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker');
	}
}
endif;
new WPGeoLocation_admin();
?>