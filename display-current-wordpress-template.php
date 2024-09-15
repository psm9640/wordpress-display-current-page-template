<?php
/*
Plugin Name: Display Current Page Template
Plugin URI: #
Description: Display current page template to logged-in users with post edit capabilities as a column in the admin view and in the lower-left corner of the browser window when viewing the front end of the site.
Author: Peter Marra
Version: 1.1.1
Author URI: https://marraman.com/
*/

/*------------------------------------------------------------------------------------------------------------*/
/*  Plugin Setup
/*------------------------------------------------------------------------------------------------------------*/

function vtw_setup() {
    if ( current_user_can( 'edit_posts' ) ) {
        add_action( 'wp_enqueue_scripts', 'vtw_enqueue_styles' );
        add_action( 'wp_footer', 'vtw_render_template_in_footer' );
    }

    $post_types = vtw_post_types();

    foreach ( $post_types as $pt ) {
        add_filter( 'manage_edit-' . $pt . '_columns', 'vtw_add_template_column' );
        add_action( 'manage_' . $pt . '_posts_custom_column', 'vtw_render_template_column_content', 10, 2 );
    }

    add_action( 'restrict_manage_posts', 'vtw_filter_templates_for_select' );
    add_filter( 'parse_query', 'vtw_filter_query_by_template' );
}
add_action( 'init', 'vtw_setup' );

/*------------------------------------------------------------------------------------------------------------*/
/*  Enqueue Styles
/*------------------------------------------------------------------------------------------------------------*/

function vtw_enqueue_styles() {
    $css_url = plugin_dir_url( __FILE__ ) . 'assets/view-template-wpengine.css';
    wp_enqueue_style( 'vtw-view-template', $css_url, array(), '1.1', 'all' );
}

/*------------------------------------------------------------------------------------------------------------*/
/*  Define post types for template filtering
/*------------------------------------------------------------------------------------------------------------*/

function vtw_post_types() {
    return array( 'page', 'post', 'product' );
}

/*------------------------------------------------------------------------------------------------------------*/
/*  Display current template on the front end
/*------------------------------------------------------------------------------------------------------------*/

function vtw_render_template_in_footer() {
    if ( !is_admin() && current_user_can( 'edit_posts' ) ) {
        $template = basename( get_page_template() );
        echo '<div id="view-template-wpengine">' . esc_html( $template ) . '</div>';
    }
}

/*------------------------------------------------------------------------------------------------------------*/
/*  Add custom column for post template in the admin view
/*------------------------------------------------------------------------------------------------------------*/

function vtw_add_template_column( $columns ) {
    $columns['vtw-page-template'] = __( 'Post Template', 'view-template-wpengine' );
    return $columns;
}

/*------------------------------------------------------------------------------------------------------------*/
/*  Render the post template in the custom column
/*------------------------------------------------------------------------------------------------------------*/

function vtw_render_template_column_content( $column_name, $post_id ) {
    if ( 'vtw-page-template' === $column_name ) {
        $template = get_page_template();
        if ( basename( $template ) === 'page.php' && get_post_type( $post_id ) === 'post' ) {
            echo esc_html( 'single.php' );
        } else {
            echo esc_html( basename( $template ) );
        }
    }
}

/*------------------------------------------------------------------------------------------------------------*/
/*  Add template filter dropdown in the admin list view
/*------------------------------------------------------------------------------------------------------------*/

function vtw_filter_templates_for_select() {
    global $typenow;

    if ( in_array( $typenow, vtw_post_types() ) ) {
        global $wpdb;

        // Get distinct template values
        $templates = $wpdb->get_col( "
            SELECT DISTINCT meta_value
            FROM $wpdb->postmeta
            WHERE meta_key = '_wp_page_template'
            AND meta_value != ''
            AND EXISTS (SELECT 1 FROM $wpdb->posts WHERE ID = post_id AND post_type = '$typenow')
            ORDER BY meta_value
        " );

        // Set current template filter value
        $current_template = isset( $_GET['template_file'] ) ? sanitize_text_field( $_GET['template_file'] ) : '';

        // Add nonce for security
        wp_nonce_field( 'vtw_template_filter', 'vtw_template_filter_nonce' );
        ?>
        <select name="template_file" id="template_file">
            <option value="all" <?php selected( 'all', $current_template ); ?>><?php _e( 'All Templates', 'view-template-wpengine' ); ?></option>
            <?php foreach ( $templates as $template ) { ?>
                <option value="<?php echo esc_attr( $template ); ?>" <?php selected( $template, $current_template ); ?>><?php echo esc_html( $template ); ?></option>
            <?php } ?>
        </select>
        <?php
    }
}

/*------------------------------------------------------------------------------------------------------------*/
/*  Modify the query to filter by selected template
/*------------------------------------------------------------------------------------------------------------*/

function vtw_filter_query_by_template( $query ) {
    global $pagenow;
    $post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';

    if ( is_admin() && 'edit.php' === $pagenow && in_array( $post_type, vtw_post_types() ) && isset( $_GET['template_file'] ) && 'all' !== $_GET['template_file'] ) {
        // Verify nonce
        if ( isset( $_GET['vtw_template_filter_nonce'] ) && wp_verify_nonce( $_GET['vtw_template_filter_nonce'], 'vtw_template_filter' ) ) {
            $template_file = sanitize_text_field( $_GET['template_file'] );
            $query->query_vars['meta_key'] = '_wp_page_template';
            $query->query_vars['meta_value'] = $template_file;
        }
    }
}
?>
