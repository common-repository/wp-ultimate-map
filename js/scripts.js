	var directionsDisplay = [];
	var directionsService = [];
	var request = [];
	var route_markers = [];
	var infowindow=[];
	var markers_arr = [];
	var routes_arr=[];
	var routes_content = [];
	var RoutesPolyline = [];
	var RoutesInfowindow = [];
	var placeAssociated = [];
	var is_clear_on_idle = 1;
	var routeChanged = [];
	var markerChanged = [];
	jQuery(document).ready(function() {
jQuery('.show-all').click(function(e){
		if(!jQuery(this).prop('checked') ){
			jQuery('.marker').attr('checked' , false);			
		}else{
			jQuery('.marker').attr('checked' , 'checked');
		}
});
jQuery('.route_by-show-all').click(function(e){
		if(!jQuery(this).prop('checked') ){
			jQuery('.route_by').attr('checked' , false);			
		}else{
			jQuery('.route_by').attr('checked' , 'checked');
		}
});
jQuery('.route-show-all').click(function(e){
		if(!jQuery(this).prop('checked') ){
			jQuery('.route').attr('checked' , false);			
		}else{
			jQuery('.route').attr('checked' , 'checked');
		}
});
		/* Place Marker */
		jQuery('input[name="markers"]').click(function(e){


			if ( !jQuery(this).prop('checked')  ) 
				jQuery('.show-all').attr('checked' , false);

			var matches = [];
			jQuery(".marker:checked").each(function() {
			    matches.push(this.value);
			});

			var data = {
					'action': 'update_markers',
					'markers': matches
				};

			
			jQuery.post(ajaxurl, data, function(response) {
				clearMarkers();
				 markers_arr = jQuery.parseJSON(response);
			routeChanged = markers_arr;
				 for (var i = 0, len = markers_arr.length; i < len; i++) {
					 var LatLng = new google.maps.LatLng(markers_arr[i].lat, markers_arr[i].lng);
					 placeMarker(LatLng , map , markers_arr[i].marker_url , markers_arr[i].content , markers_arr[i].tax_id);
				    }
				 for (var i = 0, len = markerChanged.length; i < len; i++) {
					 var LatLng = new google.maps.LatLng(markerChanged[i].lat, markerChanged[i].lng);
					 placeMarker(LatLng , map , markerChanged[i].marker_url , markerChanged[i].content , markerChanged[i].tax_id);
				    }
			});
			changePlacePage(1)
			
	});

		/* place Route By */
		jQuery('input[name="route_by"]').click(function(e){
		
			if ( !jQuery(this).prop('checked')  ) 
				jQuery('.route_by-show-all').attr('checked' , false);

			
			var matches = [];
			jQuery(".route_by:checked").each(function() {
			    matches.push(this.value);
			});

			var data = {
					'action': 'update_routes_by',
					'routes_by': matches
				};

			clearRoutes_route_by(directionsDisplay);

			jQuery.post(ajaxurl, data, function(response) {
				clearMarkers();
			
				for (var i = 0, len = routeChanged.length; i < len; i++) {
					 var LatLng = new google.maps.LatLng(routeChanged[i].lat, routeChanged[i].lng);
					 placeMarker(LatLng , map , routeChanged[i].marker_url , routeChanged[i].content , routeChanged[i].tax_id);
				    }
				 markers_arr = jQuery.parseJSON(response);
				 markerChanged = markers_arr;
				 for (var i = 0, len = markers_arr.length; i < len; i++) {
					 var LatLng = new google.maps.LatLng(markers_arr[i].lat, markers_arr[i].lng);
					 placeMarker(LatLng , map , markers_arr[i].marker_url , markers_arr[i].content , markers_arr[i].tax_id);
					if(i>0 && markers_arr[i].draw_route) {
						var start = new google.maps.LatLng(markers_arr[i-1].lat, markers_arr[i-1].lng);
					        var end = new google.maps.LatLng(markers_arr[i].lat, markers_arr[i].lng);
						calcRoute(start , end , i*11111 , markers_arr[i-1].route_color , '' , []);
					}
				    }
			});
			changePlacePage(1);
		});

		/* Place Routes */
		jQuery('input[name="route"]').click(function(e){


			if ( !jQuery(this).prop('checked')  ) 
				jQuery('.route-show-all').attr('checked' , false);

			
			var matches = [];
			jQuery(".route:checked").each(function() {
			    matches.push(this.value);
			});

			var data = {
					'action': 'update_routes',
					'routes': matches
				};


			clearRoutes(directionsDisplay);
			jQuery.post(ajaxurl, data, function(response) {

				routes_content = jQuery.parseJSON(response);
				
				for(var i=0 ; i<routes_content.length ; i++){
					routes_arr[i] = routes_content[i].coordinate;
					placeAssociated[i] = routes_content[i].place_associated;
					routes_content[i] = routes_content[i].content;
				}
				for(var i=0; i<routes_arr.length; i++ ) {
					route = routes_arr[i];
					for(var j=1;j<route.length;j++) {
						 var start = new google.maps.LatLng(route[j-1].lat, route[j-1].lng);
					     var end = new google.maps.LatLng(route[j].lat, route[j].lng);
					     if(jQuery('#marker_'+placeAssociated[i]).is(':checked') == false)
					    	 jQuery('#marker_'+placeAssociated[i]).trigger('click');
					     
					     calcRoute(start , end , j+i*100 , route[j-1].route_color , routes_content[i] , placeAssociated[i]);
					}
				}
				
			});
	});
});
	
function placeMarker(latLng, map , marker_image ,content , tax_id) {
	if(inViewport(latLng)) {
				var width=42;
				var height=50;
				/*var icon = new google.maps.MarkerImage(
					marker_image, //url
					undefined,
					undefined,
					undefined,
					new google.maps.Size(width, height) //size
					
					);
*/
				  var marker = new google.maps.Marker({
				    position: latLng,
					map: map,
					icon: marker_image ,
					labelClass: 'marker_'+tax_id ,
					animation: google.maps.Animation.DROP,
				  });
					markers.push(marker);
					var id = markers.length-1;
					infowindow[id] = new google.maps.InfoWindow({
					    content: content
					  });
					markers[id].addListener('click', function() {
							is_clear_on_idle = 0;
							infowindow[id].open(map, markers[id]);
					});
	}
}

function setMapOnAll(map) {
	  for (var i = 0; i < markers.length; i++) {
		  markers[i].setMap(map);
	  }
}

	// Removes the markers from the map, but keeps them in the array.
function clearMarkers() {
		setMapOnAll(null);
}

function calcRoute(start , end , id , color , content , placesAssociated) {

  	var bounds = new google.maps.LatLngBounds();

	

	request[id] = {
      origin: start,
      destination: end,
      travelMode: google.maps.TravelMode.DRIVING 
    };
	if(color != null) {
		var polyline = new google.maps.Polyline({
			path: [],
			strokeColor: color,
			strokeWeight: 3
		});
   directionsDisplay[id] = new google.maps.DirectionsRenderer({suppressMarkers: true ,preserveViewport: true ,polylineOptions: {
	      strokeColor: color ,
   }});
	}else {
		var polyline = new google.maps.Polyline({
			path: [],
			strokeWeight: 3
		});
		directionsDisplay[id] = new google.maps.DirectionsRenderer({suppressMarkers: true ,preserveViewport: true });
	}
    directionsDisplay[id].setMap(map);	  	    		    
    directionsService[id] = new google.maps.DirectionsService();
    
    directionsService[id].route(request[id], function(response, status) {
      if (status == google.maps.DirectionsStatus.OK) {
    	  directionsDisplay[id].setDirections(response);
    	  directionsDisplay[id].setMap(map);
    	 
    	  var legs = response.routes[0].legs;
          for (i=0;i<legs.length;i++) {
            
        	 var steps = legs[i].steps;
            for (j=0;j<steps.length;j++) {
              var nextSegment = steps[j].path;
              for (k=0;k<nextSegment.length;k++) {
                polyline.getPath().push(nextSegment[k]);
                bounds.extend(nextSegment[k]);
              }
            }
          }
          polyline.setMap(map);
    	  
    	  RoutesInfowindow[id] = new google.maps.InfoWindow({
			    content: content
			  });
		  google.maps.event.addListener(polyline, 'click', function(event) {
        	  is_clear_on_idle = 0;
        	  var marker = new google.maps.Marker({
				    position: event.latLng,
					map: map,
					icon: ' '
				  });
        	  
        	  	RoutesInfowindow[id].open(map, marker);
        	});
			
      } else {
       // alert("Directions Request from " + start.toUrlValue(6) + " to " + end.toUrlValue(6) + " failed: " + status);
      }
    });
    
    RoutesPolyline[id] = polyline
}

function clearRoutes_route_by(directionsDisplay) {
	jQuery.each(directionsDisplay, function( index, value ) {
		if(index%11111 == 0) {
			if(value !== null && typeof value == 'object') {
				directionsDisplay[index].setDirections({routes: []});
				directionsDisplay[index].setMap(null);
			}
		}
	});
	jQuery.each(RoutesPolyline, function( index, value ) {
		if(index%11111 == 0) {		
			if(value !== null && typeof value == 'object') {
				RoutesPolyline[index].setMap(null);
			}
		}
	});
}

function clearRoutes(directionsDisplay) {
	jQuery.each(directionsDisplay, function( index, value ) {
		if(index%11111 != 0) 
		if(value !== null && typeof value == 'object') {
			directionsDisplay[index].setDirections({routes: []});
			directionsDisplay[index].setMap(null);
		}
	});
	jQuery.each(RoutesPolyline, function( index, value ) {
		if(index%11111 != 0) 
		if(value !== null && typeof value == 'object') {
			RoutesPolyline[index].setMap(null);
		}
	});
	routes_arr = [];
	placeAssociated = [];
	routes_content = [];
}

function inViewport(point) {
    var bounds = map.getBounds();

    if(typeof bounds == 'undefined')
    	return 0;
    sw = bounds.getSouthWest();
    ne = bounds.getNorthEast();
    
    left = sw.lng();
    down = sw.lat();
    right = ne.lng();
    up = ne.lat();
    centerLng = map.getCenter().lng();

    leftHalf = new google.maps.LatLngBounds(
      new google.maps.LatLng(down, left),
      new google.maps.LatLng(up, centerLng)
    );

    rightHalf = new google.maps.LatLngBounds(
      new google.maps.LatLng(down, centerLng),
      new google.maps.LatLng(up, right)
    );
    return leftHalf.contains(point) || rightHalf.contains(point);
  }

function inViewPortMarker() {
	if(is_clear_on_idle) {
	clearMarkers();
	 for (var i = 0, len = routeChanged.length; i < len; i++) {
		 var LatLng = new google.maps.LatLng(routeChanged[i].lat, routeChanged[i].lng);
		 placeMarker(LatLng , map , routeChanged[i].marker_url , routeChanged[i].content, '');
	    }
	}else {

		 is_clear_on_idle = 1;
	}
}
function inViewPortRoute() {
if(is_clear_on_idle) {
	for(var i=0; i<routes_arr.length; i++ ) {
		route = routes_arr[i];
		for(var j=1;j<route.length;j++) {
			 var start = new google.maps.LatLng(route[j-1].lat, route[j-1].lng);
		     var end = new google.maps.LatLng(route[j].lat, route[j].lng);
				if(inViewport(start) && inViewport(end)) {
					directionsDisplay[j+i*100].setMap(map);
				}else {
					directionsDisplay[j+i*100].setMap(null);
				}
		}
	}
}else {
	is_clear_on_idle = 1;	
}
}
function changePlacePage(page) {
		jQuery('#place-loader').show();
		var checkedMarker = [];
		var checkedRoutes = [];
			jQuery(".marker:checked").each(function() {
			    checkedMarker.push(this.value);
			});
			jQuery(".route_by:checked").each(function() {
			    checkedRoutes.push(this.value);
			});
			
		var data = {
					'action': 'changePage',
					'markers': checkedMarker ,
					'route_by': checkedRoutes , 
					'page': page , 
					'per_page': document.getElementById('per_page').value
				};

			
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#place-listing').html(response);
				jQuery('#place-loader').hide();
			});
		return false;
}


function changePlacePageByOrder(page , order) {
		jQuery('#place-loader').show();
		var checkedMarker = [];
		var checkedRoutes = [];
			jQuery(".marker:checked").each(function() {
			    checkedMarker.push(this.value);
			});
			jQuery(".route_by:checked").each(function() {
			    checkedRoutes.push(this.value);
			});
		var data = {
					'action': 'changePage',
					'markers': checkedMarker , 
					'route_by': checkedRoutes ,
					'page': page , 
					'per_page': document.getElementById('per_page').value , 
					'order': order
				};

			
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#place-listing').html(response);
				jQuery('#place-loader').hide();
			});
		return false;
}
