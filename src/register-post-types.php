<?php
// create a place custom post type
$place = new CPT(array(
		'post_type_name' => 'place',
		'singular' => 'Place',
		'plural' => 'Places',
		'slug' => 'place'
));
// create a genre taxonomy
$place->register_taxonomy(array(
		'taxonomy_name' => 'marker',
		'singular' => 'Marker',
		'plural' => 'Markers',
		'slug' => 'marker'
));
// define the columns to appear on the admin edit screen
$place->columns(array(
		'cb' => '<input type="checkbox" />',
		'title' => __('Title'),
		'marker' => __('Marker'),
));
// create a genre taxonomy
$place->register_taxonomy(array(
		'taxonomy_name' => 'route_by',
		'singular' => 'Route By',
		'plural' => 'Route By',
		'slug' => 'route_by'
));
// define the columns to appear on the admin edit screen
$place->columns(array(
		'cb' => '<input type="checkbox" />',
		'title' => __('Title'),
		'marker' => __('Route By'),
));

// use "pages" icon for post type
$place->menu_icon("dashicons-book-alt");



// create a route custom post type
$route = new CPT(array(
		'post_type_name' => 'route',
		'singular' => 'Route',
		'plural' => 'Routes',
		'slug' => 'route'
));
// create a genre taxonomy
$route->register_taxonomy(array(
		'taxonomy_name' => 'route_taxo',
		'singular' => 'Route Type',
		'plural' => 'Route Types',
		'slug' => 'route_taxo'
));
// define the columns to appear on the admin edit screen
$route->columns(array(
		'cb' => '<input type="checkbox" />',
		'title' => __('Title'),
		'route_taxo' => __('Route'),
));
// use "pages" icon for post type
$route->menu_icon("dashicons-book-alt");
