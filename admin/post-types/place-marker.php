<?php



/**
 * Calls the class on the post edit screen.
 */
function call_CMB_Place() {
	new CMB_Place();
}

if ( is_admin() ) {
	add_action( 'load-post.php', 'call_CMB_Place' );
	add_action( 'load-post-new.php', 'call_CMB_Place' );
}

/**
 * The Class.
 */
class CMB_Place {

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
		$post_types = array('place');     //limit meta box to certain post types
		if ( in_array( $post_type, $post_types )) {
			add_meta_box(
					'place_location'
					,__( 'Place a marker', TEXT_DOMAIN )
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
		$lat = sanitize_text_field($_POST['place_lat']);
		$lng = sanitize_text_field($_POST['place_lng']);
		// Update the meta field.
		update_post_meta($post_id, 'place_location', array('lat'=>$lat , 'lng'=>$lng));
		
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'myplugin_inner_custom_box', 'myplugin_inner_custom_box_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		
		$place_location = get_post_meta( $post->ID, 'place_location', true );
		
		if(is_array($place_location)) {
			$lat = $place_location['lat'];
			$lng = $place_location['lng'];
		}else {
			$focus_point = get_option('focus_point');
			if($focus_point == false || $focus_point['lat'] == '' || $focus_point['lng'] == '')  {
				$focus_point['lat'] = '34.00';
				$focus_point['lng'] = '76.00';
			}
			
			$lat = $focus_point['lat'];
			$lng = $focus_point['lng'] ;
		}
		
		$zoom_level = get_option('wpgl_zoom_level');
		if($zoom_level == false) {
			$zoom_level = 4;
		}
		
		// Display the form, using the current value.
		echo	'<input type="hidden" id="place_lat" name="place_lat"';
		echo ' value="'.$lat.'" />
				<input type="hidden" id="place_lng" name="place_lng"';
		echo ' value="'.$lng.'" />';
		echo '	<input id="pac-input" class="controls" type="text" placeholder="Search Box">
				<div class="map"><div id="place"></div></div>
				  <script>

var map;
var markers = [];
function initMap() {
				
var initLatLng = {lat: '.$lat.', lng: '.$lng.'};
				
  map = new google.maps.Map(document.getElementById(\'place\'), {
    center: initLatLng,
    zoom: '.$zoom_level.'
  });
		
	placeMarkerAndPanTo(initLatLng , map);
		
 map.addListener(\'click\', function(e) {
	clearMarkers(\'\');
    placeMarkerAndPanTo(e.latLng, map);
	jQuery(\'#place_lat\').val(e.latLng.lat());
	jQuery(\'#place_lng\').val(e.latLng.lng());
  });	
	place_search(map);
}    </script>';
		WPGeoLocation_admin::load_google_map_js();
	}
}

// Add the fields to the "presenters" taxonomy, using our callback function
add_action( 'marker_edit_form_fields', 'marker_taxonomy_custom_fields', 10, 2 );

add_action('marker_add_form_fields', 'marker_taxonomy_custom_fields' , 10, 2);
// Save the changes made on the "presenters" taxonomy, using our callback function
add_action( 'edited_marker', 'save_taxonomy_custom_fields', 10, 2 );

add_action( 'create_marker', 'save_taxonomy_custom_fields', 10, 2 );
// A callback function to save our extra taxonomy field(s)
function save_taxonomy_custom_fields( $term_id ) {
	if ( isset( $_POST['term_meta'] ) ) {
		$t_id = $term_id;
		$term_meta = get_option( "taxonomy_term_$t_id" );
		$cat_keys = array_keys( $_POST['term_meta'] );
		foreach ( $cat_keys as $key ){
			if ( isset( $_POST['term_meta'][$key] ) ){
				$term_meta[$key] = $_POST['term_meta'][$key];
			}
		}
		error_log(print_r($term_meta , true));
		//save the option array
		update_option( "taxonomy_term_$t_id", $term_meta );
	}
}

// A callback function to add a custom field to our "presenters" taxonomy
function marker_taxonomy_custom_fields($tag) {
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
	<button class="set_custom_images button">Upload marker</button>
    </td>
	<td>
	<img alt="" id="marker-img" src="<?php echo $term_meta['marker_image'] ? $term_meta['marker_image'] : ''; ?>">
	<input type="hidden" value="<?php echo $term_meta['marker_image'] ? $term_meta['marker_image'] : ''; ?>" class="regular-text process_custom_images" id="process_custom_images" name="term_meta[marker_image]" max="" min="1" step="1">
	</td>
</tr>
<script type="text/javascript">
jQuery(function($){
    if ($('.set_custom_images').length > 0) {
        if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
            $('.wrap').on('click', '.set_custom_images', function(e) {
                e.preventDefault();
                var button = $(this);
                var id = button.prev();
                wp.media.editor.send.attachment = function(props, attachment) {
                    id.val(attachment.url);
                    jQuery('#process_custom_images').val(attachment.url);
                    $('#marker-img').attr('src' , attachment.url);
                };
                wp.media.editor.open(button);
                return false;
            });
        }
    };
	
});
</script>
<?php
 
} 
add_action ( 'admin_enqueue_scripts', function () {
	if (is_admin ())
		wp_enqueue_media ();
} );




// Add the fields to the "presenters" taxonomy, using our callback function
add_action( 'route_by_edit_form_fields', 'route_by_taxonomy_custom_fields', 10, 2 );

add_action('route_by_add_form_fields', 'route_by_taxonomy_custom_fields' , 10, 2);
// Save the changes made on the "presenters" taxonomy, using our callback function
add_action( 'edited_route_by', 'save_route_by_taxonomy_custom_fields', 10, 2 );

add_action( 'create_route_by', 'save_route_by_taxonomy_custom_fields', 10, 2 );
// A callback function to save our extra taxonomy field(s)
function save_route_by_taxonomy_custom_fields( $term_id ) {
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
function route_by_taxonomy_custom_fields($tag) {
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
