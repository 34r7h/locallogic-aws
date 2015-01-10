add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_uri(), array( 'parent-style' ) );
}

// Add Shortcode
function current_userFn() {

	// Code
<?php
    $current_user = wp_get_current_user();
    /**
     * @example Safe usage: $current_user = wp_get_current_user();
     * if ( !($current_user instanceof WP_User) )
     *     return;
     */
    echo 'Username: ' . $current_user->user_login . '<br />';
    echo 'User email: ' . $current_user->user_email . '<br />';
    echo 'User first name: ' . $current_user->user_firstname . '<br />';
    echo 'User last name: ' . $current_user->user_lastname . '<br />';
    echo 'User display name: ' . $current_user->display_name . '<br />';
    echo 'User ID: ' . $current_user->ID . '<br />';
?>
}
add_shortcode( 'current_user', 'current_userFn' );