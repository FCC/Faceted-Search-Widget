<?php
/*
Plugin Name: Faceted Search Widget
Plugin URI: http://
Description: Sidebar Widget to allow filtering indexes by builtin and custom taxonomies
Version: 1.0
Author: The Federal Communications Commission
Author URI: http://fcc.gov/developers
License: GPL2
*/

/**
 * Widget to propegate sidebar list of taxonomies and terms
 * @param array $args args passed to widget
 */
function fcc_refine_widget( $args ) {

	//verify that this is either an archive or search results
	if ( !is_archive() && !is_search() )
		return;

	//grab widget args and wp_query
	global $wp_query;
	extract( $args ); 
	
	echo $before_widget;
	echo $before_title; ?>Refine Search<?php echo $after_title;

	$taxs = get_taxonomies( null,  'objects' ); ?>
	<ul>
	<?php foreach ($taxs as $tax) { 
	
	//If user has already filterd by this taxonomy, do not display
	if ( $wp_query->get( $tax->name ) != FALSE )
		continue;
	
	//verify taxonomy is public and queryable
	if ( !$tax->query_var || !$tax->public )
		continue;
		
	//verify taxonomy has terms associated with it
	$terms = get_terms( $tax->name );
	if ( sizeof( $terms ) == 0)
		continue;
		
	?>
		<li> <?php echo $tax->labels->name; ?>
			<ul>
			<?php foreach ( $terms as $term ) { 
				
				//appened this query to wp_query and count the number of posts returned
				$args = $wp_query->query;
				$args[ $tax->name ] = $term->slug;
				$query = new WP_Query( $args );
				
				//If this term has no posts, don't display the link
				if ( !$query->found_posts )
					continue;		
				?>
				<li><a href="<?php echo esc_url( add_query_arg( $tax->query_var, $term->slug) ); ?>"><?php echo $term->name; ?></a> (<?php echo $query->found_posts; ?>)</li>
			<?php } ?>
			</ul>
		</li>
	<?php } ?>
	</ul>
	<?php echo $after_widget; 
}
	
/**
 * Callback to register the sidebar widget
 */
function fcc_register_widget() {
	wp_register_sidebar_widget('refine-widget', 'Refine Widget', 'fcc_refine_widget' );
}

//hook for register_widget callback
add_action( 'init', 'fcc_register_widget' );

?>

