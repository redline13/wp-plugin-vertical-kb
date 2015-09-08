<?php
/**
 * Plugin Name: Vertical Knowledge Base
 * Description: Supports 1 short code with parameters to control the listing of pages.
 * Version: 1.0
 * Author: Richard Friedman (Author of updated version with backward support)
 * Author URI: http://github.com/richardfriedman
 * (Maintained original license)
 * License: GNU General Public License v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */


// Load the plugin's text domain
function vskb_init() {
	load_plugin_textdomain( 'vskb', false, dirname( plugin_basename( __FILE__ ) ) . '/translation' );
}
add_action('plugins_loaded', 'vskb_init');


// Enqueues plugin scripts
function vskb_scripts() {
	if(!is_admin()) {
		wp_enqueue_style('vskb_style', plugins_url('vskb_style.css',__FILE__));
	}
}
add_action('wp_enqueue_scripts', 'vskb_scripts');


// Creating the shortcode for four columns
function vskb_columns( $vskb_cats, $columns, $subcats = false, $excludes = null, $includes = null ) {
	$return = "";

	$columnNames = ['zero','one','two','three','four','five','six','seven','eight','nine'];
	$return .= '<div id="vskb-'.$columnNames[$columns].'">'.'<ul class="x-vskb-cat-list">';
	$content = '';

	// Get list of ICONs or images attached to the KB page, these can be icons.
	$media = get_attached_media( 'image' );
	$icons = [];
	foreach ( $media as $key => $val ) {
		$icons[ $val->post_name ] = $val->guid;
	}

  $js = '';
	$count = 0;
	foreach ($vskb_cats as $cat) {

		if ( !empty( $excludes ) && in_array( $cat->slug, $excludes ) ) {
			continue;
		} else if ( !empty( $includes ) && !in_array( $cat->slug, $includes ) ) {
			continue;
		}

		// For each category we keep map in JS
		$js .= 'cats["'.$cat->slug.'"] = '.$count.';';

		$img = '';
		if ( !empty($icons[$cat->slug . '-ico'] ) ) {
			$img = '<img class="category-icon" src="' . $icons[$cat->slug . '-ico'] . '">';
		}

		// $return .= '<ul class="vskb-cat-list"><li class="vskb-cat-name"><a href="'. get_category_link( $cat->cat_ID ) .'" title="'. $cat->name .'" >'. $cat->name .'</a></li>';
		$return .= '<li class="x-vskb-cat-name"><a href="#'. $cat->slug .'" title="'. $cat->name .'" >'. $img . $cat->name .'</a></li>';

		$catColumn = $subCats ? 'category__in' : 'cat';
		$vskb_args = array(
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'posts_per_page' => -1, // -1 means list all posts
			$catColumn => $cat->cat_ID // list posts from all categories and posts from sub category will be listed underneath their parent category
		);

		$vskb_posts = get_posts($vskb_args);

    $content .= '<div id="'.$cat->slug.'"><ul class="vskb-cat-list">';
		foreach( $vskb_posts AS $single_post ) :
			$content .=  '<li class="vskb-post-name">';
			$content .=  '<a href="'. get_permalink( $single_post->ID ) .'" rel="bookmark" title="'. get_the_title( $single_post->ID ) .'">'. get_the_title( $single_post->ID ) .'</a>';
			$content .=  '</li>';
		endforeach;
		$content .= '</ul></div>';

		$count++;
	}
	$return .= '</ul>' . $content;
	$return .= '</div>';

	// Add jQuery
	$return .= '<script>
	jQuery( function() {
		var cats = {};
		'. $js .'
		console.log( window.location.hash, cats );
		whichTab = 0;
		jQuery( "#vskb-'.$columnNames[$columns].'" ).tabs( { active : whichTab }).addClass( "ui-tabs-vertical ui-helper-clearfix" );
		jQuery( "#vskb-'.$columnNames[$columns].' li.x-vskb-cat-name" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
	});
	</script>';

	return $return;

}

function shortCodeHandler ( $attrs, $content, $handler ) {

	if ( empty( $attrs ) ) {
		$attrs = [];
		$attrs['subcats'] = false;
		$attrs['columns'] = 'four';
		$attrs['excludes'] = null;
		$attrs['includes'] = null;
	}

	$excludes = null;
	if ( !empty( $attrs['excludes']) ) {
		$excludes = explode( ',', $attrs['excludes'] );
	}

	$includes = null;
	if ( !empty( $attrs['includes']) ) {
		$includes = explode( ',', $attrs['includes'] );
	}

	$catSearch = 'hide_empty=1&orderby=name&order=asc';
	if ( !empty( $attrs['subcats'] ) && $attrs['subcats'] == 'true' ) {
		$vskb_cats = get_categories('parent=0&hide_empty=0');
		$result = vskb_columns( $vskb_cats, $attrs['columns'], true, $excludes, $includes );
	} else {
		$vskb_cats = get_categories('hide_empty=0');
		$result = vskb_columns( $vskb_cats, $attrs['columns'], false, $excludes, $includes );
	}

	return $result;
}

/**
 * [SHORTCODE subcats=true ]
 * subcats leave blank otherwise
 */
add_shortcode('knowledgebase', 'shortCodeHandler');
