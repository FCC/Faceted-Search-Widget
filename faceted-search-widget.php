<?php
/*
Plugin Name: Faceted Search Widget
Plugin URI: http://
Description: Sidebar Widget to allow filtering indexes by builtin and custom taxonomies
Version: 1.6
Author: The Federal Communications Commission
Author URI: http://fcc.gov/developers
License: GPL2
*/


class FCC_Refine_Widget extends WP_Widget {

	private $defaults = array(
			'title' => '',
			'depth' => '0',
			'contentdiv' => false,
			'orderby' => 'name',
		);
		
	/**
	 * Constructor
	 */
	Function FCC_Refine_Widget() {
	    parent::WP_Widget(false, $name = 'Faceted Search Widget');
	    
	    //can't i18n outside of function
	    $this->defaults['title'] = __( 'Refine', 'faceted-search-widget' );
	    	    
	}

	/**
	 * Widget to propegate sidebar list of taxonomies and terms
	 * @param array $args args passed to widget
	 * @param reference $instance the widget instance
	 */
	function widget( $args, $instance ) {
    
		//verify that this is either an archive or search results
		if ( is_404() || is_single() || is_attachment() || is_page() )
			return;

		if ( $instance['contentdiv'] ) : ?>
		<script>
		faceted_search_widget = <?php echo json_encode( array( '.' . $this->widget_options['classname'], $instance['contentdiv'] ) ); ?>;
		jQuery(document).ready(function(a){a(faceted_search_widget[0]+" a").live("click",function(d){d.preventDefault();var b=a(this).attr("href");a.each(faceted_search_widget,function(b,e){a(e).animate({opacity:0})});a.ajax({url:b,context:document.body,success:function(d){var e=a("<div />").append(d.replace(/<script(.|\s)*?\/script>/g,""));a.each(faceted_search_widget,function(b,c){a(c).html(a(e).find(c).html())});a.each(faceted_search_widget,function(b,c){a(c).animate({opacity:1})});history.pushState({page:b},
b,b)}});return!1})});
		</script>
		<?php
		endif;

		//grab widget args and wp_query
		global $wp_query;
		extract( $args ); 

		$title = apply_filters( 'widget_title', $instance['title'] );
        ?>
			<?php echo $before_widget; ?>
				<?php if ( $title )
					echo $before_title . $title . $after_title;

		$taxs = get_taxonomies( array( 'public' => true, 'query_var' => true ),  'objects' ); ?>
		<ul>
		<?php foreach ($taxs as $tax) { 

		//Non-Hierarchical taxonomy with term already filtered (no futher filtering)
		if ( !$tax->hierarchical && $this->tax_in_query( $tax->name ) ) {
			continue;
			
		//Hierarchical taxonomy with term filtered (filter down to children)
		} else if ( $tax->hierarchical && $this->tax_in_query( $tax->name ) ) {
			$termID = term_exists( get_query_var( $tax->query_var ) );
			$terms = get_terms( $tax->name, array( 'child_of' => $termID ) );
			
		//No filters, get all terms
		} else {
			$terms = get_terms( $tax->name );
		}	

		//verify taxonomy has terms associated with it
		if ( sizeof( $terms ) == 0 )
			continue;
		
		add_filter( 'term_link', array( &$this, 'term_link_filter'), 10, 3 );
		add_filter( 'get_terms', array( &$this, 'get_terms_filter'), 10, 3 );
		
		wp_list_categories( array( 
								'taxonomy' => $tax->name, 
								'show_count' => true,
								'depth' => $instance['depth'],
								'title_li' => $tax->labels->name,
								'orderby' => $instance['orderby'],
							) );

		remove_filter( 'term_link', array( &$this, 'term_link_filter' ) );
		remove_filter( 'get_terms', array( &$this, 'get_terms_filter') );

	 	} ?>
		</ul>
		<?php  echo $after_widget; 
	}
	
	/**
	 * Function to process changes to the widge title
	 * @param array $old the old title
	 * @param array $new the new title
	 * @return array the new title array
	 */
	function update( $new, $old ) {
		$instance = $old;
		$instance['title'] = strip_tags( $new['title'] );
		$instance['contentdiv'] = strip_tags( $new['contentdiv'] );
		$instance['depth'] = (int) $new['depth'];
		$instance['orderby'] = $new['orderby'];
        return $instance;
	}
	
	/**
	 * Callback to generate the title form for widgets.php
	 * @param reference $instance the widget instance
	 */
	function form( $instance ) { 
	
		foreach ( $this->defaults as $key => $value )
			if ( !isset( $instance[ $key ] ) )
				$instance[ $key ] = $value;
	?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'faceted-search-widget' ); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" /><br />
          <span class="description"><?php _e( 'Title to display above the widget', 'faceted-search-widget' ); ?></span>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('depth'); ?>"><?php _e('Depth:', 'faceted-search-widget' ); ?></label> 
          <input class="small-text" id="<?php echo $this->get_field_id('depth'); ?>" name="<?php echo $this->get_field_name('depth'); ?>" type="text" value="<?php echo esc_attr( $instance['depth'] );; ?>" /><br />
          <span class="description"><?php _e( 'Number of levels to show at a time within hierarchical taxonomies like categories', 'faceted-search-widget' ); ?></span>
        </p>
        <p>
        	<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order terms by:', 'faceted-search-widget' ); ?></label>
        	<select class="widefat" id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>">
        		<?php foreach ( array( 'name' => __( 'Name', 'faceted-search-widget' ), 'count' => __( 'Count', 'faceted-search-widget' )) as $option => $label ) { ?>
        			<option value="<?php echo $option; ?>" <?php selected( $option, $instance['orderby'] ); ?>><?php echo $label; ?></option>
        		<?php } ?>
        	</select>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('contentdiv'); ?>"><?php _e('Content Div:', 'faceted-search-widget' ); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('contentdiv'); ?>" name="<?php echo $this->get_field_name('contentdiv'); ?>" type="text" value="<?php echo esc_attr( $instance['contentdiv'] );; ?>" /><br />
          <span class="description"><?php _e( 'jQuery selector of DIV containing your template\'s posts, leave blank to disable ajax search', 'faceted-search-widget' ); ?></span>
        </p>
        
        <?php 
    }
    
    /**
     * Makes term links query args rather than absolute links
     * @param string $termlink the original link
     * @param array $term the term object
     * @param string $taxonomy the taxonomy slug
     * @return string the modified link
     */
    function term_link_filter( $termlink, $term, $taxonomy ) {
    	$tax = get_taxonomy( $taxonomy );
  		return esc_url( add_query_arg( $tax->query_var, $term->slug ) );
    } 
    
    /**
     * Filters term list to only terms within current view, and modifies post count
     * @param array $terms the original terms list
     * @param array $taxonomies the taxonomy
     * @param array $args args originally passed
     * @return array the modified terms list
     */
    function get_terms_filter( $terms, $taxonomies, $args ) {
    	  
    	global $wp_query;

    	//safe to assume one because filter is added immediately before use
    	$tax = get_taxonomy( $taxonomies[0] );
    	
    	foreach ( $terms as $id => &$term ) {
    	
	    	$tax_query = $wp_query->tax_query->queries;
    		$tax_query['relationship'] = 'AND';

			$tax_query[] =	array( 	
    							'taxonomy' 	=> 	$tax->name,
    							'field' 	=> 	'slug',
    							'terms'		=> 	$term->slug,
    							);
    							
			$query = new WP_Query( array( 'tax_query' => $tax_query ) );

			//If this term has no posts, don't display the link
			if ( !$query->found_posts )
				unset( $terms[ $id ] );
			else 		
				$terms[$id]->count = $query->found_posts;
			
    	}
    
    	return $terms;
    }
    
    /**
     * Checks the global WP_Query Object to see if the taxonomy query is being queried
     * Used to prevent already filtered taxonomies from being displayed
     * @param string $tax the taxonomy slug
     * @return bool true if in the query, otherwise false
     * @since 1.4
     */
    function tax_in_query( $tax ) {
    	global $wp_query;
    	
    	if ( !isset( $wp_query->tax_query ) || !isset( $wp_query->tax_query->queries ) )
    		return false;
    	
    	foreach ( $wp_query->tax_query->queries as $query )
    		if ( $query['taxonomy'] == $tax )
    			return true;
    			
    	return false;
    	
    }
}

/**
 * Register the sidebar widget
 */
add_action('widgets_init', create_function('', 'return register_widget("FCC_Refine_Widget");'));
