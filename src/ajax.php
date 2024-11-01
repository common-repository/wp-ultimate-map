<?php 

if(!defined('ABSPATH')) 
	exit();

if ( !class_exists('WPGL_Ajax')) :
	class WPGL_Ajax {
		
		private $actions = array();
		public function __construct() {
			$this->actions = array(
					'update_markers'	=> true ,
					'update_routes'		=> true ,
					'update_routes_by'	=> true ,
					'changePage' 		=> true
			);
			
			foreach ($this->actions as $action => $priv){
				add_action( 'wp_ajax_'.$action, array($this, $action));
				if($priv == true)
					add_action( 'wp_ajax_nopriv_'.$action, array($this, $action));
			}
		}
		
		public function update_routes() {
			$route_types = isset($_POST['routes']) ? $_POST['routes'] : array();
			$to_return = array();
			foreach ($route_types as $type) {
				$posts_array = get_posts(
						array(
								'posts_per_page' => -1,
								'post_type' => 'route',
								'tax_query' => array(
										array(
												'taxonomy' => 'route_taxo',
												'field' => 'term_id',
												'terms' => $type,
										)
								)
						)
				);
				$term_meta = get_option( "taxonomy_term_$type" );
				foreach ($posts_array as $post) {
					$route_coordinate = get_post_meta( $post->ID, 'route_coordinate', true );
					$places_associated = get_post_meta( $post->ID, 'places_with_routes', true );
					$content = '<div class="iw-container"><div class="iw-title">'.$post->post_title.'</div><div class="iw-content">'.$post->post_content .'</div></div>';
						
					if(is_array($route_coordinate)) {
						foreach ($route_coordinate as $key => $coordinate) {
							$coordinate = explode(',', $coordinate);
							$route_coordinate[$key] = array(
									'lat'=>$coordinate[0] ,
									'lng'=>$coordinate[1] ,
									'route_color'=>$term_meta['route_color']
							);
						}
					}else {
						$route_coordinate = array();	
					}
					$to_return[] = array('coordinate'=>$route_coordinate , 'content'=>$content , 'place_associated' => $places_associated);
				}
				
			}
			echo json_encode($to_return);
			die();
		}
		
		public function update_markers() {
			$markers = isset($_POST['markers']) ? $_POST['markers'] : array();
			$to_return = array();
			foreach ($markers as $marker) {
				$posts_array = get_posts(
						array(
								'posts_per_page' => -1,
								'post_type' => 'place',
								'tax_query' => array(
										array(
												'taxonomy' => 'marker',
												'field' => 'term_id',
												'terms' => $marker,
										)
								)
						)
				);
				$term_meta = get_option( "taxonomy_term_$marker" );
				foreach ($posts_array as $post) {
					
					$place_location = get_post_meta( $post->ID, 'place_location', true );
					
					if(is_array($place_location)) {
						$lat = $place_location['lat'];
						$lng = $place_location['lng'];
					}else {
						$lat = '';
						$lng = '';
					}
					
					$content = '<div class="iw-container"><div class="iw-title">'.$post->post_title.'</div><div class="iw-content">'.$post->post_content.'</div></div>';
					if($lat != '' && $lng != '')
					$to_return[] = array(
							'marker_url' => $term_meta['marker_image'] ,
							'lat'		 =>	$lat ,
							'lng'		 => $lng ,
							'content'	=> $content ,
							'tax_id'	=> $marker ,
					);
				}
			}
			echo json_encode($to_return);
			die();
			
		}
		public function update_routes_by() {
			$markers = isset($_POST['routes_by']) ? $_POST['routes_by'] : array();
			$to_return = array();
			foreach ($markers as $marker) {
				$posts_array = get_posts(
						array(
								'posts_per_page' => -1,
								'post_type' => 'place',
								'tax_query' => array(
										array(
												'taxonomy' => 'route_by',
												'field' => 'term_id',
												'terms' => $marker,
										)
								)
						)
				);
				$first = 1;
				$term_meta = get_option( "taxonomy_term_$marker" );
				foreach ($posts_array as $post) {
					
					$place_location = get_post_meta( $post->ID, 'place_location', true );

					$marker_term = wp_get_post_terms( $post->ID, 'marker');
					if(isset($marker_term[0]))
						$marker_term_meta = get_option( "taxonomy_term_".$marker_term[0]->term_id );
					else{
						$marker_term_meta = get_option( "" );
					}
					if(is_array($place_location)) {
						$lat = $place_location['lat'];
						$lng = $place_location['lng'];
					}else {
						$lat = '';
						$lng = '';
					}
					
					$content = '<div class="iw-container"><div class="iw-title">'.$post->post_title.'</div><div class="iw-content">'.$post->post_content.'</div></div>';
					if($lat != '' && $lng != '')
					$to_return[] = array(
							'marker_url' => $marker_term_meta['marker_image'] ,
							'route_color' => $term_meta['route_color'],
							'lat'		 =>	$lat ,
							'lng'		 => $lng ,
							'content'	=> $content ,
							'draw_route'	=> !$first ,
							'tax_id'	=> $marker ,
					);
					
					$first = 0;
				}
			}
			echo json_encode($to_return);
			die();
			
		}

		public function changePage() {
			$markers = isset($_POST['markers']) ? $_POST['markers'] : array();
			$routeTax = isset($_POST['route_by']) ? $_POST['route_by'] : array();
			$page = isset($_POST['page']) ? $_POST['page'] : 1;
			$per_page = isset($_POST['per_page']) ? $_POST['per_page'] : 1;
			$order = isset($_POST['order']) ? $_POST['order'] : 'title';

$post_per_page= $per_page;


$args_paging = array( 'post_type' => 'place' , 'posts_per_page' => -1, 'tax_query' => array(
		'relation' => 'OR' ,
            array(
                'taxonomy' => 'marker',
                'field' => 'term_id',
                'terms' => $markers,
            ) ,
		 array(
                'taxonomy' => 'route_by',
                'field' => 'term_id',
                'terms' => $routeTax,
            )
        ) );

$places_paging = get_posts( $args_paging );


$total_places = count($places_paging);
$pages_count = $total_places/ $post_per_page;


$args = array( 'post_type' => 'place' , 'posts_per_page' => $post_per_page , 'offset' => $page-1 , 'tax_query' => array(
            'relation' => 'OR' ,
            array(
                'taxonomy' => 'marker',
                'field' => 'term_id',
                'terms' => $markers,
            ) ,
		 array(
                'taxonomy' => 'route_by',
                'field' => 'term_id',
                'terms' => $routeTax,
            )
        ) , 
		'orderby'          => $order);

$places = get_posts( $args );


?>
		<div class="overlay" id="place-loader" style="display:none" >
			<img src="<?php echo WP_GEO_URL.'/images/pw_loader.gif'?>" />
			
		</div>		

			<div class="place-filter">
				<div class="filters"><?php _e('Order By:' ,TEXT_DOMAIN); ?> <a href="javascript:void(0)" onclick="changePlacePageByOrder(1 , 'title');" ><?php  _e('Title' ,TEXT_DOMAIN); ?></a> </div>
				<div class="per-page" onchange="changePlacePage(1);">
					<select name="per_page" id="per_page">
						<option <?php echo ($per_page==10) ? 'selected="selected"' : ''; ?> value="10">10</option>
						<option <?php echo ($per_page==20) ? 'selected="selected"' : ''; ?> value="20">20</option>
						<option <?php echo ($per_page==30) ? 'selected="selected"' : ''; ?> value="30">30</option>
						<option <?php echo ($per_page==40) ? 'selected="selected"' : ''; ?> value="40">40</option>
						<option <?php echo ($per_page==50) ? 'selected="selected"' : ''; ?> value="50">50</option>			
					</select>
				</div>
			</div>

			<ul class="places">

				<?php foreach($places as $place) {
	//print_r($place);
 ?>
					<li>
						<div class="list-main">	
							<div class="list-r">
								<?php echo get_the_post_thumbnail( $place->ID, array(64 ,64) ); ?>
							</div>
							<div class="list-l">
								<div><a href="<?php echo get_permalink($place->ID); ?>"><span class="list-title"><?php echo $place->post_title; ?></span></a></div>
								<div class="place-info">
								<?php echo substr($place->post_excerpt , 0 , 100); ?>								<span class="read-more"><a href="<?php echo get_permalink($place->ID); ?>"><?php _e('Read More' , TEXT_DOMAIN); ?></a></span>
								</div>
							</div>
						</div>
					</li>
				<?php } ?>		
			</ul>

			<div class="place-nave">
				<ul>
					<?php 
						for($page=$pages_count; $page>=1; $page--) {
							echo '<a href="javascript:void(0)" onclick="changePlacePage('.$page.');" ><li>'.$page.'</li></a>';
						}	
					?>
				</ul>
			</div>
<?php
die();
		}

	}
endif;
new WPGL_Ajax();
?>
