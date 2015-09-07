<?php
// Creating the shortcode for four columns
function vskb_columns( $vskb_cats, $id, $subcats = false ) {

	$return = "";

	$return .= '<div id="'.$id.'">';

	foreach ($vskb_cats as $cat) :

		$return .= '<ul class="vskb-cat-list"><li class="vskb-cat-name"><a href="'. get_category_link( $cat->cat_ID ) .'" title="'. $cat->name .'" >'. $cat->name .'</a></li>';

		$catColumn = $subCats ? 'category__in' : 'cat';
		$vskb_args = array(
			'posts_per_page' => -1, // -1 means list all posts
			$catColumn => $cat->cat_ID // list posts from all categories and posts from sub category will be listed underneath their parent category
		);

		$vskb_posts = get_posts($vskb_args);

		foreach( $vskb_posts AS $single_post ) :
			$return .=  '<li class="vskb-post-name">';
			$return .=  '<a href="'. get_permalink( $single_post->ID ) .'" rel="bookmark" title="'. get_the_title( $single_post->ID ) .'">'. get_the_title( $single_post->ID ) .'</a>';
			$return .=  '</li>';
		endforeach;

		$return .=  '</ul>';

	endforeach;

	$return .= '</div>';

	return $return;

}
