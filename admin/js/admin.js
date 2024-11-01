	var markers = [];
	var markersRoutes = [];
	var directionsDisplay = [];
	var directionsService = [];
	var request = [];
	var map;
	function clearMarkers(type) {
		setMapOnAll(null , type);
	}
	function setMapOnAll(map , type) {
		if(type == '')
			for (var i = 0; i < markers.length; i++) {
				markers[i].setMap(map);
			}
		else 
			for (var i = 0; i < markersRoutes.length; i++) {
				markersRoutes[i].setMap(map);
			}
	}
	function placeMarkerWithId(latLng, map ,id) {
		var marker = new google.maps.Marker({
			position: latLng,
			draggable: true,		
		    map: map
		  });
		
		markers[id] = marker;
		google.maps.event.addListener(markers[id], 'dragend', function() {
				jQuery('#focus-lat').val(markers[id].position.lat());
				jQuery('#focus-lng').val(markers[id].position.lng());
	    });
		map.panTo(latLng);
	}
	function placeMarkerAndPanTo(latLng, map) {
		var marker = new google.maps.Marker({
			position: latLng,
			draggable: true,		
		    map: map
		  });
		
		google.maps.event.addListener(marker, 'dragend', function() {
			jQuery('#place_lat').val(marker.position.lat());
			jQuery('#place_lng').val(marker.position.lng());
    });
		markers.push(marker);
		map.panTo(latLng);
	}
	function placeMarkerWithLabel( latLng, map , label ){
		var marker = new google.maps.Marker({
			position: latLng,
			draggable: true,	
			label: label.toString(),
			map: map,
			id: label-1
		  });
		google.maps.event.addListener(marker, 'dragend', function() {
			jQuery('#latlng').html('');
			for( var i = 0 ; i < markers.length ; i++ ) {
				jQuery('#latlng').append('<input type="hidden" id="marker_'+(i+1)+'" name="latlng[]" value="'+markers[i].position.lat()+','+markers[i].position.lng()+'" />');
			}
			for( var i = 0 ; i < markers.length ; i++ ) {
				if( markers.length >= 2 ) {
					var start = new google.maps.LatLng(markers[i].position.lat() ,markers[i].position.lng());
					var end = new google.maps.LatLng(markers[i+1].position.lat() ,markers[i+1].position.lng());
					//jQuery('#marker_'+(i+1)).html('<input type="hidden" id="marker_'+(i+1)+'" name="latlng[]" value="'+markers[i+1].position.lat()+','+markers[i+1].position.lng()+'" />');
					if(i < markers.length-1)
						calcRoute(start , end , parseInt(markers[i+1].id));
				}
			}
        });
		markers.push(marker);
		jQuery('#latlng').append('<input type="hidden" id="marker_'+markers.length+'" name="latlng[]" value="'+latLng.lat()+','+latLng.lng()+'" />');
		
		if(parseInt(markers.length) >= 2) {
			var start = new google.maps.LatLng(markers[markers.length-2].position.lat() ,markers[markers.length-2].position.lng());
			var end = new google.maps.LatLng(markers[markers.length-1].position.lat() ,markers[markers.length-1].position.lng());
			calcRoute(start , end , parseInt(markers[markers.length-1].id));
		}
		map.panTo(latLng);	
	}
	
	function calcRoute(start , end , id) {
	   request[id] = {
	      origin: start,
	      destination: end,
	      travelMode: google.maps.TravelMode.DRIVING 
	    };
	  clearRoutes(directionsDisplay[id]);
	   directionsDisplay[id] = new google.maps.DirectionsRenderer({suppressMarkers: true});
	    directionsDisplay[id].setMap(map);	  	    		    
	    directionsService[id] = new google.maps.DirectionsService();
	    directionsService[id].route(request[id], function(response, status) {
	      if (status == google.maps.DirectionsStatus.OK) {
	    	  directionsDisplay[id].setDirections(response);
	    	  directionsDisplay[id].setMap(map);
	      } else {
	       // alert("Directions Request from " + start.toUrlValue(6) + " to " + end.toUrlValue(6) + " failed: " + status);
	      }
	    });
	  }
	
	function clearRoutes(directionsDisplay) {
		 if(directionsDisplay !== null && typeof directionsDisplay == 'object'){
			   directionsDisplay.setDirections({routes: []});
			   directionsDisplay.setMap(null);
		   }
	}
	
	function clearAll(){
		for (var i = 0; i < directionsDisplay.length; i++) {
			clearRoutes(directionsDisplay[i]);
		}
		clearMarkers('');
		markers =[];
		directionsDisplay= [];
		directionsService = []; 
		jQuery('#latlng').html('');
	}
	function place_search(map){
		   var input = document.getElementById('pac-input');
		   var searchBox = new google.maps.places.SearchBox(input);
		   map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

		   map.addListener('bounds_changed', function() {
		   		searchBox.setBounds(map.getBounds());
		   });
	 	   jQuery('#pac-input').keypress(function(e){
		   		var code = e.keycode || e.which;
		    	if(code == 13) {
		    		e.preventDefault();
		    	}
		    });
		    var search_markers = [];
		     // [START region_getplaces]
		     // Listen for the event fired when the user selects a prediction and retrieve
		     // more details for that place.
		    searchBox.addListener('places_changed', function() {
		       var places = searchBox.getPlaces();
		       if (places.length == 0) {
		         return;
		       }
		       search_markers.forEach(function(marker) {
		         marker.setMap(null);
		       });
		       search_markers = [];
		       // For each place, get the icon, name and location.
		       var bounds = new google.maps.LatLngBounds();
		       places.forEach(function(place) {
		         var icon = {
		           url: place.icon,
		           size: new google.maps.Size(71, 71),
		           origin: new google.maps.Point(0, 0),
		           anchor: new google.maps.Point(17, 34),
		           scaledSize: new google.maps.Size(25, 25)
		         };

		         // Create a marker for each place.
		         search_markers.push(new google.maps.Marker({
		           map: map,
		           icon: icon,
		           title: place.name,
		           position: place.geometry.location
		         }));

		         if (place.geometry.viewport) {
		           // Only geocodes have viewport.
		           bounds.union(place.geometry.viewport);
		         } else {
		           bounds.extend(place.geometry.location);
		         }
		       });
		       map.fitBounds(bounds);
		     });
		  }
	function placeMarker(latLng, map , marker_image ,content) {
		if(inViewport(latLng)) {
					  var marker = new google.maps.Marker({
					    position: latLng,
						map: map,
						icon: marker_image
					  });
					  markersRoutes.push(marker);						
		}
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