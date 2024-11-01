<?php


/**
 * Calls the class on the post edit screen.
 */
function call_CMB_Route() {
	new CMB_Route();
}

if ( is_admin() ) {
	add_action( 'load-post.php', 'call_CMB_Route' );
	add_action( 'load-post-new.php', 'call_CMB_Route' );
}

/**
 * The Class.
 */
class CMB_Route {

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {
		$post_types = array('route');     //limit meta box to certain post types
		if ( in_array( $post_type, $post_types )) {
			add_meta_box(
					'route_marker'
					,__( 'Place a marker for route', TEXT_DOMAIN )
					,array( $this, 'render_meta_box_content' )
					,$post_type
					,'advanced'
					,'high'
			);
		}
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['myplugin_inner_custom_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['myplugin_inner_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'myplugin_inner_custom_box' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
		//     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// Sanitize the user input.
		if(isset($_POST['latlng'])) {
			$latlng = $_POST['latlng'];
		}else{
			$latlng = array();
		}
		
		if(isset($_POST['places_with_routes']) && $_POST['places_with_routes'] != '') {
			update_post_meta($post_id, 'places_with_routes', $_POST['places_with_routes']);
		}
		// Update the meta field.
		update_post_meta($post_id, 'route_coordinate', $latlng);
		
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );

		$focus_point = get_option('focus_point');
		if($focus_point == false )  {
			$focus_point['lat'] = '34.00';
			$focus_point['lng'] = '76.00';
		}
		
		// Use get_post_meta to retrieve an existing value from the database.
		
		$route_coordinate = get_post_meta( $post->ID, 'route_coordinate', true );
		
		if(is_array($route_coordinate)) {
			foreach ($route_coordinate as $key => $coordinate) {
				$coordinate = explode(',', $coordinate);
				$route_coordinate[$key] = array(
										'lat'=>$coordinate[0] ,
										'lng'=>$coordinate[1]
										);
			}
		}
		
		$zoom_level = get_option('wpgl_zoom_level');
		if($zoom_level == false) {
			$zoom_level = 4;
		}
		if(get_post_meta($post->ID, 'places_with_routes'  ,true)) {
			$place_with_route = get_post_meta($post->ID, 'places_with_routes'  ,true);
		}else {
			$place_with_route = '0';
		}
		// Display the form, using the current value.
		?>
		<table>
  <tr>
    <th>Associate Markers with Routes</th>
    <th> 
    	<select id="places_with_routes" name="places_with_routes">
    	<option value="">Select Places</option>
    <?php 
    $terms = get_terms( 'marker' );
			 if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
			 	foreach ( $terms as $term ) {
       				echo '<option value="'.$term->term_id.'">' . $term->name . '</option>';
        		}
 			}
 	?></select>
 			</th>
  </tr>
</table>

		<input type="button" class="controls map-btn" name="clear_route_marker" value="Clear Route" onclick="clearAll()"/>
		
		<input id="pac-input" class="controls" type="text" placeholder="Search Box">
		<div class="map"><div id="map-canvas"></div></div>
		<div id="latlng"></div>
<script>
var routeLatLng= <?php echo json_encode($route_coordinate ); ?>;
function initMap() {
	  var focus = new google.maps.LatLng(<?php echo $focus_point['lat']; ?>, <?php echo $focus_point['lng']; ?>);
	    var mapOptions = {
	      zoom: <?php echo $zoom_level; ?>,
	      center: focus
	    };
	    map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

	    google.maps.event.addListener(map, 'click', function(event) {
		    	var label = markers.length+1;
	    	   placeMarkerWithLabel(event.latLng , map , label);
	    });

	    google.maps.event.addListenerOnce(map, 'tilesloaded', function(){
	    	jQuery('#places_with_routes').trigger('change');
	    });
	   	    
	    for(i=0;i<routeLatLng.length;i++) {
	    	 var latLng = new google.maps.LatLng(routeLatLng[i].lat, routeLatLng[i].lng);
	    	 var label = i+1;
	    	 placeMarkerWithLabel(latLng , map , label);
		}
	    
	    /*
	     *  Display and process search box
	     */
	     place_search(map);

	}

	jQuery(function(){
		var place_with_route = <?php echo $place_with_route  ?>;
		jQuery('#places_with_routes').change(function(){
			var matches = [];
		    matches.push(jQuery('#places_with_routes').val());
		
		var data = {
				'action': 'update_markers',
				'markers': matches
			};

		
		jQuery.post(ajaxurl, data, function(response) {
			clearMarkers('MarkerRoutes');
			 markers_arr = jQuery.parseJSON(response);
			 for (var i = 0, len = markers_arr.length; i < len; i++) {
				 var LatLng = new google.maps.LatLng(markers_arr[i].lat, markers_arr[i].lng);
				 placeMarker(LatLng , map , markers_arr[i].marker_url , markers_arr[i].content);
			    }
		});
						
		});
		jQuery('#places_with_routes').val(place_with_route);
		
	});
</script>
		<?php 
		WPGeoLocation_admin::load_google_map_js();
	}
}

// Add the fields to the "presenters" taxonomy, using our callback function
add_action( 'route_taxo_edit_form_fields', 'route_taxonomy_custom_fields', 10, 2 );

add_action('route_taxo_add_form_fields', 'route_taxonomy_custom_fields' , 10, 2);
// Save the changes made on the "presenters" taxonomy, using our callback function
add_action( 'edited_route_taxo', 'save_route_taxo_taxonomy_custom_fields', 10, 2 );

add_action( 'create_route_taxo', 'save_route_taxo_taxonomy_custom_fields', 10, 2 );
// A callback function to save our extra taxonomy field(s)
function save_route_taxo_taxonomy_custom_fields( $term_id ) {
	if ( isset( $_POST['term_meta'] ) ) {
		$t_id = $term_id;
		$term_meta = get_option( "taxonomy_term_$t_id" );
		$cat_keys = array_keys( $_POST['term_meta'] );
		foreach ( $cat_keys as $key ){
			if ( isset( $_POST['term_meta'][$key] ) ){
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		//save the option array
		update_option( "taxonomy_term_$t_id", $term_meta );
	}
}

// A callback function to add a custom field to our "presenters" taxonomy
function route_taxonomy_custom_fields($tag) {
	// Check for existing taxonomy meta for the term you're editing
	if(is_object($tag)) {
		$t_id = $tag->term_id; // Get the ID of the term you're editing
		$term_meta = get_option( "taxonomy_term_$t_id" ); // Do the check
	}else {
		$term_meta = 0;
	}
	?>  
<tr>
	<td>
	<input type="text" name="term_meta[route_color]" value="<?php echo $term_meta['route_color']?>" class="color-field" >
	<script type="text/javascript">
	    jQuery(function() {
	    	jQuery('.color-field').wpColorPicker();
	    });
	</script>
    </td>
</tr>
<?php
 
} 
add_action ( 'admin_enqueue_scripts', function () {
	if (is_admin ())
		wp_enqueue_media ();
} );

?>