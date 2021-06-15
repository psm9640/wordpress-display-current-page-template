<?php

/*
Plugin Name: Display Current Page Template
Plugin URI: #
Description: Display current page template to logged in admin role both as a column in admin view and in lower left corner of window when viewing front end of site.
Author: Peter Marra
Version: 1.0
Author URI: https://marraman.com/
*/

/*------------------------------------------------------------------------------------------------------------*/
/*
/*	Plugin Setup
/*
/*------------------------------------------------------------------------------------------------------------*/

function vtw_setup() {

	if( current_user_can( 'edit_posts' ) ):
		$css = plugin_dir_url( __FILE__ ) . 'assets/view-template-wpengine.css';	
		wp_enqueue_style( 'vtw-view-template', $css, array(), '1.0.2', 'all' );
		add_action('wp_footer','vtw_view_page_template');
	endif;
	
	$post_types = vtw_post_types();

	foreach($post_types AS $pt):
		add_filter( 'manage_edit-'.$pt.'_columns', 'vtw_page_columns' );
		add_action( 'manage_'.$pt.'_posts_custom_column', 'vtw_page_column_content', 10, 2 );
	endforeach;
	
	add_action( 'restrict_manage_posts', 'vtw_filter_templates_for_select' );
	add_filter( 'parse_query', 'vtw_get_templates_by_meta_value' );
	
}

add_action( 'init', 'vtw_setup' );

/*------------------------------------------------------------------------------------------------------------*/
/*
/*	Define which post types we want this filtering available for 
/*
/*------------------------------------------------------------------------------------------------------------*/

function vtw_post_types() {
	return array('page','post','product');	
}


/*------------------------------------------------------------------------------------------------------------*/
/*
/*	Front End Display Function 
/*
/*------------------------------------------------------------------------------------------------------------*/

function vtw_view_page_template() {
	
	if ( !is_admin() ):
	
		global $template;
		$get_file_name = basename( $template );	
	
		echo '<div id="view-template-wpengine">' . basename( $get_file_name ) . '</div>';
	endif;

}


/*------------------------------------------------------------------------------------------------------------*/
/*
/*	Setup Custom Columns for Admin
/*
/*------------------------------------------------------------------------------------------------------------*/

function vtw_page_columns($columns) {
    $columns['vtw-page-template'] =__('Post Template','vtw-template-columns');
    return $columns;
}

/*------------------------------------------------------------------------------------------------------------*/
/*
/*	Populate Columns with Content
/*
/*------------------------------------------------------------------------------------------------------------*/

function vtw_page_column_content( $column_name, $post_id ) {
	
	global $post;
    if ( 'vtw-page-template' != $column_name )
        return;

 	$the_template = ( basename( get_page_template() ) == 'page.php' AND $post->post_type == 'post' ) ? 'single.php' : basename( get_page_template() );
    echo $the_template;

}


/*------------------------------------------------------------------------------------------------------------*/
/*
/*	Filter Post/Page Templates from the meta query 
/*
/*------------------------------------------------------------------------------------------------------------*/

function vtw_filter_templates_for_select() {

	global $typenow;
	global $wp_query;
 	
    if( in_array( $typenow, vtw_post_types() ) ) {
      
      global $wpdb;
		$templates = $wpdb->get_col("
			SELECT DISTINCT wp_postmeta.meta_value
			FROM wp_postmeta
			LEFT JOIN wp_posts ON wp_postmeta.post_id = wp_posts.ID
			WHERE wp_postmeta.meta_key = '_wp_page_template'
			AND wp_postmeta.meta_value != ''
			AND wp_posts.post_type = '" . $typenow . "'
			ORDER BY wp_postmeta.meta_value
		");
      
      
      $current_template = '';
      if( isset( $_GET['template_file'] ) ) {
        $current_template = $_GET['template_file']; // Check if option has been selected
      } ?>
		<select name="template_file" id="template_file">
			<option value="all" <?php selected( 'all', $current_template ); ?>><?php _e( 'All Templates', 'post-template' ); ?></option>
			<?php foreach( $templates as $v ) { ?>
			<option value="<?php echo esc_attr( $v ); ?>" <?php selected( $v, $current_template ); ?>><?php echo esc_attr( $v ); ?></option>
			<?php } ?>
		</select>
<?php }
}

/*------------------------------------------------------------------------------------------------------------*/
/*
/*	Update and run the query
/*
/*------------------------------------------------------------------------------------------------------------*/

function vtw_get_templates_by_meta_value( $query ) {
  global $pagenow;
  // Get the post type
  $post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';
  if ( is_admin() && $pagenow=='edit.php' && in_array( $post_type, vtw_post_types() ) && isset( $_GET['template_file'] ) && $_GET['template_file'] !='all' ) {
    $query->query_vars['meta_key'] = '_wp_page_template';
    $query->query_vars['meta_value'] = $_GET['template_file'];
    $query->query_vars['meta_compare'] = '=';
  }
}

?>