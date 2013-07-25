<?php
/*
Plugin Name: PG custom carousel
Plugin URI: 
Description: A custom post type for choosing images and content which outputs the PG website.  Requires jQuery and modified jshowoff plugin be installed.
Version: .8
Author: Jason White
Author URI: 
License: GPLv2
*/

// Custom Post Type Setup
add_action( 'init', 'pgcc_post_type' );

function pgcc_post_type() {
	$labels = array(
		'name' => 'Carousel Images',
		'singular_name' => 'Carousel Image',
		'add_new' => 'Add New Image',
		'add_new_item' => 'Add New Carousel Image',
		'edit_item' => 'Edit Carousel Image',
		'new_item' => 'New Carousel Image',
		'view_item' => 'View Carousel Image',
		'search_items' => 'Search Carousel Images',
		'not_found' =>  'No Carousel Image',
		'not_found_in_trash' => 'No Carousel Images found in Trash', 
		'parent_item_colon' => '',
		'menu_name' => 'Carousel'
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'exclude_from_search' => true,
		'publicly_queryable' => false,
		'show_ui' => true, 
		'show_in_menu' => true, 
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'page',
		'has_archive' => true, 
		'hierarchical' => false,
		'menu_position' => 21,
		'supports' => array('title','excerpt','thumbnail', 'page-attributes')
	); 
	register_post_type('pgcc', $args);
}


// Add theme support for featured images if not already present
// http://wordpress.stackexchange.com/questions/23839/using-add-theme-support-inside-a-plugin
function pgcc_addFeaturedImageSupport() {
	$supportedTypes = get_theme_support( 'post-thumbnails' );
	if( $supportedTypes === false )
		add_theme_support( 'post-thumbnails', array( 'pgcc' ) );               
	elseif( is_array( $supportedTypes ) ) {
		$supportedTypes[0][] = 'pgcc';
		add_theme_support( 'post-thumbnails', $supportedTypes[0] );
	}
}

add_action( 'after_setup_theme', 'pgcc_addFeaturedImageSupport');

// FRONT END

// Shortcodes
function pgcc_shortcode($atts, $content = null) {
	// Set default shortcode attributes
	$defaults = array(
	  'post_type' => 'pgcc',
		'interval' => '5000',
		'showcaption' => 'true',
		'showcontrols' => 'true'
	);

	// Parse incomming $atts into an array and merge it with $defaults
	$atts = shortcode_atts($defaults, $atts);

	return pgcc_frontend($atts);
}

// Shortcodes
function pg_shortcode($atts, $content = null) {
	// Set default shortcode attributes
	$defaults = array(
	  'post_type' => 'pgcc',
		'interval' => '5000',
		'showcaption' => 'true',
		'showcontrols' => 'true'
	);

	// Parse incomming $atts into an array and merge it with $defaults
	$atts = shortcode_atts($defaults, $atts);

	return pg_frontend($atts);
}
add_shortcode('pg-carousel', 'pgcc_shortcode');



// Display latest WftC
function pgcc_frontend($atts){
	$id = rand(0, 999); // use a random ID so that the CSS IDs work with multiple on one page
	$args = array( 'post_type' => $atts['post_type'], 'orderby' => 'menu_order', 'order' => 'ASC');
	$loop = new WP_Query( $args );
	$images = array();
	while ( $loop->have_posts() ) {
		$loop->the_post();
		if ( '' != get_the_post_thumbnail() ) {
			$title = get_the_title();
			$content = get_the_excerpt();
			$image = get_the_post_thumbnail( get_the_ID(), 'full' );
			$images[] = array('title' => $title, 'content' => $content, 'image' => $image);
		}
	}
	if(count($images) > 0){
		ob_start();
		?>
		<div id="slide-container">

			<?php foreach ($images as $key => $image) { ?>
				<div class="slide">
          <?php echo $image['image']; ?>
          <div class="desc">
            <?php echo $image['content']; ?>
          </div>
				</div>
			<?php } ?>

		</div>

<?php }
	$output = ob_get_contents();
	ob_end_clean();
	
	// Restore original Post Data
	wp_reset_postdata();	
	
	return $output;
}

// Call the carousel in javascript, else it won't start scrolling on its own
function pgcc_footer_js() {
?>
<script type="text/javascript">
  var afterEffect = function(){
    var img = $('#slide-container .slide:last img');
    var bk_img = img.attr('src').replace(".png", "_bk.jpg");

    //$('#body-container').css("background-image", "url('" + bk_img + "')");
    $('#bg-image-container').html("<img src='" + bk_img + "' />");
              img.hide().delay(1500).fadeIn(2000);
  };

  $(document).ready(function(){ $('#slide-container').jshowoff({
      cssClass: 'thumbFeatures',
      effect: 'slideLeft',
      speed: 4500,
      changeSpeed: 1500,
      afterTransition: afterEffect
  }); });
</script>
<?php
}
add_action('wp_footer', 'pgcc_footer_js');

?>