		<script type="text/javascript">
			jQuery(function(){
				var select_all_places = <?php echo $select_all_places ? '1' : '0'; ?>;
				var select_all_routes = <?php echo $select_all_routes ? '1' : '0'; ?>;
				if(select_all_places)
					jQuery('.show-all').trigger('click');
				if(select_all_routes) { 
					jQuery('.route-show-all').trigger('click');
					jQuery('.route_by-show-all').trigger('click');
				}
			});
		</script>
		<div class="map-container">
			<div class="map">
				<div id="place"></div>
			</div>
			<div class="map-sidebar">
			<?php 
			if(empty($markers_terms)) {
				 $terms = get_terms( 'marker' );
			}
			else {
				foreach($markers_terms as $term_id){
					$terms[] = get_term_by('id' , $term_id , 'marker');
				}
			}
			 if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
			 	echo '<span class="sidebar-header">'.__('Places' , TEXT_DOMAIN). '</span>';
     			echo '<ul>';
     			echo '<li><input type="checkbox" class="marker show-all" name="markers" value="-1" /> '.__('Show All' , TEXT_DOMAIN).'</li>';
     			foreach ( $terms as $term ) {
       				echo '<li><input type="checkbox" id="marker_'.$term->term_id.'" class="marker" name="markers" value="'.$term->term_id.'" /> ' . __($term->name , TEXT_DOMAIN) . '</li>';
        		}
 	    		echo '</ul>';
 			}
 			if(empty($routes_terms)) {
	 			$terms = get_terms( 'route_taxo' );
				$terms_route_by = get_terms( 'route_by' );
			}else {
				foreach($routes_terms as $term_id){
					$terms[] = get_term_by('id' , $term_id , 'route');
				}
			}
 			if ( (! empty( $terms ) && ! is_wp_error( $terms )) ){
 				echo '<span class="sidebar-header">'.__('Routes' , TEXT_DOMAIN).'</span>';
 				echo '<ul>';
 				echo '<li><input type="checkbox" class="routes route-show-all" name="route" value="-1" />'. __('Show All' , TEXT_DOMAIN) . '</li>';
 				foreach ( $terms as $term ) {
 					echo '<li><input type="checkbox" class="route" name="route" value="'.$term->term_id.'" /> ' . __($term->name , TEXT_DOMAIN) . '</li>';
 				}
 				echo '</ul>';
 			}

			if ((! empty( $terms_route_by ) && ! is_wp_error( $terms_route_by ))  ){
 				echo '<span class="sidebar-header">'.__('Routes By' , TEXT_DOMAIN). '</span>';
 				echo '<ul>';
 				echo '<li><input type="checkbox" class="routes route_by-show-all" name="route_by" value="-1" /> '.__('Show All' , TEXT_DOMAIN).'</li>';
 				
				foreach ( $terms_route_by as $term ) {
 					echo '<li><input type="checkbox" class="route_by" name="route_by" value="'.$term->term_id.'" /> ' . __($term->name , TEXT_DOMAIN) . '</li>';
 				}
 				echo '</ul>';
 			}
 ?>
			</div>
		<script>
		var markers = [];
		var ajaxurl='<?php echo admin_url('admin-ajax.php')?>';
		function mapLocation() {
			var initLatLng = {lat: <?php echo $atts['focus_lat']; ?> , lng: <?php echo $atts['focus_lng']; ?>};
			map = new google.maps.Map(document.getElementById('place'), {
				center: initLatLng,
				scrollwheel: false ,
		    	zoom: <?php echo $atts['zoom_level']; ?>
				});
			google.maps.event.addListener(map, 'idle', function() {
				inViewPortMarker();
				inViewPortRoute();
			});
		}		
		</script>
<div class="place-list" id="place-listing">
<div class="overlay" id="place-loader" style="display:none" >
			<img src="<?php echo WP_GEO_URL.'/images/pw_loader.gif'?>" />
			
		</div>	

<select name="per_page" id="per_page" onchange="changePlacePage(1)" style="display:none">
						<option value="10" selected="selected">10</option>
						<option value="20">20</option>
						<option value="30">30</option>
						<option value="40">40</option>
						<option value="50">50</option>				
					</select>
</div>
