<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Get the selected template, fallback to 'nordic-clean'
$netfett_selected_template = get_option( 'netfett_maint_template', 'nordic-clean' );
$netfett_template_file     = plugin_dir_path( __FILE__ ) . 'templates/' . sanitize_file_name( $netfett_selected_template ) . '.php';

if ( file_exists( $netfett_template_file ) ) {
    include $netfett_template_file;
} else {
    // Fallback to nordic-clean if file doesn't exist
    $netfett_fallback_file = plugin_dir_path( __FILE__ ) . 'templates/nordic-clean.php';
    if ( file_exists( $netfett_fallback_file ) ) {
        include $netfett_fallback_file;
    } else {
        // Safe fallback if all template files are missing
        wp_die( 
            '<h1>' . esc_html__( 'Website under maintenance', 'netfett-maintenance' ) . '</h1><p>' . esc_html__( 'Our website is currently undergoing maintenance. Please try again later.', 'netfett-maintenance' ) . '</p>', 
            esc_html__( 'Maintenance Mode - Netfett Maintenance', 'netfett-maintenance' ), 
            array( 'response' => 503 ) 
        );
    }
}
