<?php
/**
 * Netfett Maintenance Uninstall File
 *
 * This file is run automatically by WordPress when the plugin is deleted by the user.
 * It deletes all plugin options from the database if the corresponding option was selected.
 */

// If uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Check if the user selected to delete data on uninstall
if ( get_option( 'netfett_maint_delete_on_uninstall' ) === '1' ) {
    // Delete all options
    delete_option( 'netfett_maint_enabled' );
    delete_option( 'netfett_maint_template' );
    delete_option( 'netfett_maint_logo' );
    delete_option( 'netfett_maint_countdown' );
    delete_option( 'netfett_maint_auto_deactivate' );
    delete_option( 'netfett_maint_bypass_duration' );
    delete_option( 'netfett_maint_title' );
    delete_option( 'netfett_maint_message' );
    delete_option( 'netfett_maint_email' );
    delete_option( 'netfett_maint_social_instagram' );
    delete_option( 'netfett_maint_social_facebook' );
    delete_option( 'netfett_maint_social_linkedin' );
    delete_option( 'netfett_maint_social_twitter' );
    delete_option( 'my_maintenance_bypass_token' );
    delete_option( 'netfett_maint_delete_on_uninstall' );
}
