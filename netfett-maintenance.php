<?php
/**
 * Plugin Name: Netfett Maintenance
 * Description: Displays a beautiful, modern maintenance page when activated or enabled.
 * Version: 1.0.1
 * Author: Netfett
 * Author URI: https://netfett.de
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * Text Domain: netfett-maintenance
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// PHP Version Check
if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
    add_action( 'admin_notices', function() {
        ?>
        <div class="notice notice-error">
            <p><?php echo esc_html( sprintf( __( 'Netfett Maintenance requires PHP version 7.4 or higher. Your current version is %s.', 'netfett-maintenance' ), PHP_VERSION ) ); ?></p>
        </div>
        <?php
    } );
    return;
}

class Netfett_Maintenance {
    
    public function __construct() {
        // Redirection & Bypass Link
        add_action( 'init', array( $this, 'check_bypass_link' ) );
        add_action( 'template_redirect', array( $this, 'render_maintenance_page' ) );
        
        // Admin Settings Page
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_init', array( $this, 'handle_activation_redirect' ) );
        add_action( 'admin_init', array( $this, 'handle_regenerate_bypass_token' ) );
        add_action( 'admin_init', array( $this, 'handle_reset_defaults' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        
        // Admin notices & status indicators
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_indicator' ), 999 );
        add_action( 'admin_notices', array( $this, 'admin_notice_indicator' ) );

        // Settings link in plugins table
        add_filter( 'plugin_action_links_netfett-maintenance/netfett-maintenance.php', array( $this, 'add_plugin_action_links' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_action_links' ) );
    }

    public function enqueue_admin_assets( $hook ): void {
        if ( $hook === 'settings_page_netfett-maintenance' ) {
            wp_enqueue_media();
        }
    }
    
    /**
     * Checks if the maintenance mode is enabled.
     */
    private function is_maintenance_active(): bool {
        $enabled = get_option( 'netfett_maint_enabled', '0' );
        if ( $enabled !== '1' ) {
            return false;
        }

        // Auto-deactivation check if countdown is set and option is checked
        $auto_deactivate = get_option( 'netfett_maint_auto_deactivate', '0' );
        if ( $auto_deactivate === '1' ) {
            $countdown = get_option( 'netfett_maint_countdown' );
            if ( ! empty( $countdown ) ) {
                try {
                    $timezone = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( get_option( 'timezone_string' ) ?: 'UTC' );
                    $date = new DateTime( $countdown, $timezone );
                    $countdown_time = $date->getTimestamp();
                    
                    if ( $countdown_time && $countdown_time <= time() ) {
                        // Time has expired. Turn off maintenance mode programmatically.
                        update_option( 'netfett_maint_enabled', '0' );
                        return false;
                    }
                } catch ( Exception $e ) {
                    // Fallback to strtotime if DateTime parsing fails
                    $countdown_time = strtotime( $countdown );
                    if ( $countdown_time && $countdown_time <= time() ) {
                        update_option( 'netfett_maint_enabled', '0' );
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Redirects non-logged-in/non-admin users to the maintenance page.
     */
    public function render_maintenance_page() {
        // Live preview check for administrators
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $is_preview_get = isset( $_GET['preview_maintenance'] ) && $_GET['preview_maintenance'] === '1';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $preview_nonce = isset( $_GET['preview_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['preview_nonce'] ) ) : '';
        $is_preview = is_user_logged_in() && current_user_can( 'manage_options' ) && $is_preview_get && wp_verify_nonce( $preview_nonce, 'netfett_preview' );

        if ( ! $is_preview ) {
            if ( ! $this->is_maintenance_active() ) {
                return;
            }

            // If the bypass is active (cookie exists and is valid), load the normal site
            if ( $this->is_bypass_active() ) {
                return;
            }

            // Allow logged-in administrators to bypass the maintenance page
            if ( current_user_can( 'manage_options' ) ) {
                return;
            }
        }

        // Do not redirect wp-login.php, administration requests or specific system files
        if ( $GLOBALS['pagenow'] === 'wp-login.php' || is_admin() ) {
            return;
        }

        // Send 503 Service Unavailable headers for SEO protection
        $protocol = isset( $_SERVER['SERVER_PROTOCOL'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) ) : 'HTTP/1.0';
        if ( 'HTTP/1.1' !== $protocol && 'HTTP/1.0' !== $protocol ) {
            $protocol = 'HTTP/1.0';
        }
        header( "$protocol 503 Service Unavailable", true, 503 );
        header( 'Content-Type: text/html; charset=utf-8' );
        header( 'Retry-After: 3600' );

        // Load the beautiful template
        $template_path = plugin_dir_path( __FILE__ ) . 'maintenance-template.php';
        if ( file_exists( $template_path ) ) {
            include $template_path;
        } else {
            // Safe fallback if the template file is missing
            wp_die( 
                '<h1>' . esc_html__( 'Website under maintenance', 'netfett-maintenance' ) . '</h1><p>' . esc_html__( 'Our website is currently undergoing maintenance. Please try again later.', 'netfett-maintenance' ) . '</p>', 
                esc_html__( 'Maintenance Mode - Netfett Maintenance', 'netfett-maintenance' ), 
                array( 'response' => 503 ) 
            );
        }
        exit;
    }

    /**
     * Add settings menu page under Settings -> Netfett Maintenance
     */
    public function add_settings_page() {
        add_options_page(
            esc_html__( 'Netfett Maintenance', 'netfett-maintenance' ),
            esc_html__( 'Netfett Maintenance', 'netfett-maintenance' ),
            'manage_options',
            'netfett-maintenance',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register settings in the database.
     */
    public function register_settings() {
        register_setting( 'netfett_maint_group', 'netfett_maint_enabled', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '0',
        ) );
        register_setting( 'netfett_maint_group', 'netfett_maint_template', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'nordic-clean',
        ) );
        register_setting( 'netfett_maint_group', 'netfett_maint_logo', array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        ) );
        register_setting( 'netfett_maint_group', 'netfett_maint_countdown', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ) );
        register_setting( 'netfett_maint_group', 'netfett_maint_auto_deactivate', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '0',
        ) );
        register_setting( 'netfett_maint_group', 'netfett_maint_bypass_duration', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '7d',
        ) );
        register_setting( 'netfett_maint_group', 'netfett_maint_title', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => __( 'We will be back soon!', 'netfett-maintenance' ),
        ) );
        register_setting( 'netfett_maint_group', 'netfett_maint_message', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default'           => __( 'Our website is currently undergoing maintenance. We are working on something great and will be back for you shortly.', 'netfett-maintenance' ),
        ) );
        register_setting( 'netfett_maint_group', 'netfett_maint_email', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_email',
            'default'           => get_option( 'admin_email' ),
        ) );
        register_setting( 'netfett_maint_group', 'netfett_maint_social_instagram', array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        ) );
        register_setting( 'netfett_maint_group', 'netfett_maint_social_facebook', array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        ) );
        register_setting( 'netfett_maint_group', 'netfett_maint_social_linkedin', array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        ) );
        register_setting( 'netfett_maint_group', 'netfett_maint_social_twitter', array(
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default'           => '',
        ) );
        register_setting( 'netfett_maint_group', 'netfett_maint_delete_on_uninstall', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '0',
        ) );
    }

    /**
     * Render the admin settings page form.
     */
    public function render_settings_page() {
        ?>
        <div class="wrap netfett-maint-admin-wrap" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;">
            <h1 style="font-size: 24px; font-weight: 600; color: #1d2327; margin-bottom: 5px;"><?php esc_html_e( 'Netfett Maintenance for WordPress', 'netfett-maintenance' ); ?></h1>
            
            <?php
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( isset( $_GET['settings-reset'] ) && $_GET['settings-reset'] === 'true' ) : ?>
                <div class="notice notice-success is-dismissible" style="margin: 15px 0 0 0; max-width: 800px; box-sizing: border-box;">
                    <p><?php esc_html_e( 'All settings have been successfully reset to default values.', 'netfett-maintenance' ); ?></p>
                </div>
            <?php endif; ?>
            <p style="color: #646970; font-size: 14px; margin-top: 0; margin-bottom: 25px;"><?php esc_html_e( 'Manage the maintenance status of your website. When active, visitors will see a modern and responsive maintenance page.', 'netfett-maintenance' ); ?></p>
            
            <form method="post" action="options.php" style="max-width: 800px; background: #fff; padding: 30px; border-radius: 8px; border: 1px solid #c3c4c7; box-shadow: 0 1px 15px rgba(0,0,0,0.04); margin-top: 20px;">
                <?php settings_fields( 'netfett_maint_group' ); ?>
                <?php do_settings_sections( 'netfett_maint_group' ); ?>
                
                <table class="form-table" role="presentation" style="margin-top: 0;">
                    <tr valign="top">
                        <th scope="row" style="width: 220px; font-weight: 600; font-size: 14px; padding: 20px 10px 20px 0;"><?php esc_html_e( 'Maintenance Mode Status', 'netfett-maintenance' ); ?></th>
                        <td style="padding: 15px 10px;">
                            <label class="netfett-switch" style="vertical-align: middle;">
                                <input type="checkbox" name="netfett_maint_enabled" value="1" <?php checked( get_option( 'netfett_maint_enabled', '0' ), '1' ); ?> />
                                <span class="netfett-slider"></span>
                            </label>
                            <span style="margin-left: 15px; font-size: 13px; color: #646970; font-style: italic; vertical-align: middle;"><?php esc_html_e( 'Toggle maintenance mode on/off.', 'netfett-maintenance' ); ?></span>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; font-size: 14px; padding: 20px 10px 20px 0;"><?php esc_html_e( 'Bypass Link (Client Preview)', 'netfett-maintenance' ); ?></th>
                        <td style="padding: 15px 10px;">
                            <?php 
                            $bypass_token = $this->get_bypass_token();
                            $bypass_url   = add_query_arg( 'bypass', $bypass_token, site_url( '/' ) );
                            $regenerate_url = wp_nonce_url( admin_url( 'options-general.php?page=netfett-maintenance&action=regenerate_bypass' ), 'netfett_regenerate_bypass' );
                            ?>
                            <div style="margin-bottom: 10px;">
                                <code id="netfett-bypass-link" style="display: inline-block; padding: 8px 12px; background: #f0f0f1; border: 1px solid #c3c4c7; border-radius: 4px; font-size: 13px; font-family: monospace; word-break: break-all; max-width: 100%; box-sizing: border-box;"><?php echo esc_url( $bypass_url ); ?></code>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                <a href="<?php echo esc_url( $regenerate_url ); ?>" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to regenerate the bypass link? All existing links and cookies will become invalid immediately.', 'netfett-maintenance' ) ); ?>');">
                                    <?php esc_html_e( 'Regenerate Link', 'netfett-maintenance' ); ?>
                                </a>
                                <button type="button" class="button button-secondary" id="netfett-copy-button">
                                    <?php esc_html_e( 'Copy Link', 'netfett-maintenance' ); ?>
                                </button>
                                
                                <span style="margin-left: 10px; color: #646970; font-size: 13px;">
                                    <?php esc_html_e( 'Bypass Duration:', 'netfett-maintenance' ); ?>
                                    <?php $current_duration = get_option( 'netfett_maint_bypass_duration', '7d' ); ?>
                                    <select name="netfett_maint_bypass_duration" style="margin-left: 5px; height: 30px; line-height: 28px; padding: 0 28px 0 8px; border-radius: 4px; vertical-align: middle; min-width: 100px;">
                                        <option value="1d" <?php selected( $current_duration, '1d' ); ?>><?php esc_html_e( '24 Hours', 'netfett-maintenance' ); ?></option>
                                        <option value="7d" <?php selected( $current_duration, '7d' ); ?>><?php esc_html_e( '7 Days', 'netfett-maintenance' ); ?></option>
                                        <option value="30d" <?php selected( $current_duration, '30d' ); ?>><?php esc_html_e( '30 Days', 'netfett-maintenance' ); ?></option>
                                    </select>
                                </span>
                            </div>
                            <p class="description" style="margin-top: 8px;">
                                <?php esc_html_e( 'Share this link with clients. Visitors who access this link can view the website without logging in. Regenerating the link will immediately invalidate the old one.', 'netfett-maintenance' ); ?>
                            </p>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const copyBtn = document.getElementById('netfett-copy-button');
                                    if (copyBtn) {
                                        copyBtn.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            const linkText = document.getElementById('netfett-bypass-link').innerText;
                                            navigator.clipboard.writeText(linkText).then(function() {
                                                const originalText = copyBtn.innerText;
                                                copyBtn.innerText = '✓ Copied!';
                                                copyBtn.style.color = '#46b450';
                                                copyBtn.style.borderColor = '#46b450';
                                                copyBtn.disabled = true;
                                                
                                                setTimeout(function() {
                                                    copyBtn.innerText = originalText;
                                                    copyBtn.style.color = '';
                                                    copyBtn.style.borderColor = '';
                                                    copyBtn.disabled = false;
                                                }, 2000);
                                            }).catch(function() {
                                                alert('<?php echo esc_js( __( 'Copy failed. Please copy the link manually.', 'netfett-maintenance' ) ); ?>');
                                            });
                                        });
                                    }
                                });
                            </script>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; font-size: 14px; padding: 20px 10px 20px 0;"><?php esc_html_e( 'Template Design', 'netfett-maintenance' ); ?></th>
                        <td style="padding: 15px 10px;">
                            <?php 
                            $selected_template = get_option( 'netfett_maint_template', 'nordic-clean' );
                            ?>
                            <div class="netfett-templates-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; max-width: 700px; margin-bottom: 15px;">
                                
                                <!-- Nordic Clean -->
                                <label class="netfett-template-card <?php echo ($selected_template === 'nordic-clean') ? 'active' : ''; ?>" style="cursor: pointer; position: relative; border: 2px solid <?php echo ($selected_template === 'nordic-clean') ? '#2271b1' : '#c3c4c7'; ?>; border-radius: 8px; overflow: hidden; background: #fff; transition: all 0.2s ease-in-out; display: block;">
                                    <input type="radio" name="netfett_maint_template" value="nordic-clean" <?php checked( $selected_template, 'nordic-clean' ); ?> style="position: absolute; opacity: 0; pointer-events: none;" />
                                    <!-- Miniature Preview -->
                                    <div class="preview-mini nordic-clean-mini" style="height: 120px; background: #f8fafc; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                                        <div class="mini-card" style="width: 70%; height: 50px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 2px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4px; box-sizing: border-box;">
                                            <div style="width: 55%; height: 4px; background: #0f172a; border-radius: 1px; margin-bottom: 4px;"></div>
                                            <div style="width: 75%; height: 2px; background: #64748b; border-radius: 1px; margin-bottom: 2px;"></div>
                                            <div style="width: 60%; height: 2px; background: #64748b; border-radius: 1px;"></div>
                                        </div>
                                    </div>
                                    <div class="card-info" style="padding: 12px; border-top: 1px solid #e5e7eb; text-align: center;">
                                        <strong style="font-size: 13px; display: block; color: #1d2327; margin-bottom: 4px;">Nordic Clean</strong>
                                        <span style="font-size: 11px; color: #646970;"><?php esc_html_e( 'Minimal Light Vibe', 'netfett-maintenance' ); ?></span>
                                    </div>
                                </label>

                                <!-- Cosmic Dark -->
                                <label class="netfett-template-card <?php echo ($selected_template === 'cosmic-dark') ? 'active' : ''; ?>" style="cursor: pointer; position: relative; border: 2px solid <?php echo ($selected_template === 'cosmic-dark') ? '#2271b1' : '#c3c4c7'; ?>; border-radius: 8px; overflow: hidden; background: #fff; transition: all 0.2s ease-in-out; display: block;">
                                    <input type="radio" name="netfett_maint_template" value="cosmic-dark" <?php checked( $selected_template, 'cosmic-dark' ); ?> style="position: absolute; opacity: 0; pointer-events: none;" />
                                    <!-- Miniature Preview -->
                                    <div class="preview-mini cosmic-dark-mini" style="height: 120px; background: radial-gradient(circle, #1e1b4b 0%, #111827 60%, #0b0f19 100%); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                                        <div class="mini-blob blob1" style="position: absolute; width: 40px; height: 40px; background: #6366f1; border-radius: 50%; filter: blur(10px); opacity: 0.4; top: 10px; left: 10px;"></div>
                                        <div class="mini-blob blob2" style="position: absolute; width: 50px; height: 50px; background: #d946ef; border-radius: 50%; filter: blur(12px); opacity: 0.4; bottom: 10px; right: 10px;"></div>
                                        <div class="mini-card" style="width: 70%; height: 50px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; backdrop-filter: blur(4px); box-shadow: 0 4px 10px rgba(0,0,0,0.3); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4px; box-sizing: border-box;">
                                            <div style="width: 55%; height: 4px; background: rgba(255,255,255,0.8); border-radius: 2px; margin-bottom: 4px;"></div>
                                            <div style="width: 75%; height: 2px; background: rgba(255,255,255,0.4); border-radius: 2px; margin-bottom: 2px;"></div>
                                            <div style="width: 60%; height: 2px; background: rgba(255,255,255,0.4); border-radius: 2px;"></div>
                                        </div>
                                    </div>
                                    <div class="card-info" style="padding: 12px; border-top: 1px solid #e5e7eb; text-align: center;">
                                        <strong style="font-size: 13px; display: block; color: #1d2327; margin-bottom: 4px;">Cosmic Dark</strong>
                                        <span style="font-size: 11px; color: #646970;"><?php esc_html_e( 'Modern Dark Glow', 'netfett-maintenance' ); ?></span>
                                    </div>
                                </label>

                                <!-- Cyberpunk Neon -->
                                <label class="netfett-template-card <?php echo ($selected_template === 'cyberpunk-neon') ? 'active' : ''; ?>" style="cursor: pointer; position: relative; border: 2px solid <?php echo ($selected_template === 'cyberpunk-neon') ? '#2271b1' : '#c3c4c7'; ?>; border-radius: 8px; overflow: hidden; background: #fff; transition: all 0.2s ease-in-out; display: block;">
                                    <input type="radio" name="netfett_maint_template" value="cyberpunk-neon" <?php checked( $selected_template, 'cyberpunk-neon' ); ?> style="position: absolute; opacity: 0; pointer-events: none;" />
                                    <!-- Miniature Preview -->
                                    <div class="preview-mini cyberpunk-neon-mini" style="height: 120px; background: #05050a; display: flex; align-items: center; justify-content: center; position: relative; background-image: linear-gradient(rgba(0, 240, 255, 0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(0, 240, 255, 0.05) 1px, transparent 1px); background-size: 8px 8px; overflow: hidden;">
                                        <div class="mini-card" style="width: 70%; height: 50px; background: rgba(5,5,10,0.9); border: 1px solid #00ffff; box-shadow: 0 0 4px #00ffff; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4px; box-sizing: border-box;">
                                            <div style="width: 55%; height: 4px; background: #ffffff; text-shadow: 0 0 2px #ff007f; margin-bottom: 4px; border-radius: 1px;"></div>
                                            <div style="width: 75%; height: 2px; background: #00ffff; margin-bottom: 2px; border-radius: 1px;"></div>
                                            <div style="width: 60%; height: 2px; background: #00ffff; border-radius: 1px;"></div>
                                        </div>
                                    </div>
                                    <div class="card-info" style="padding: 12px; border-top: 1px solid #e5e7eb; text-align: center;">
                                        <strong style="font-size: 13px; display: block; color: #1d2327; margin-bottom: 4px;">Cyberpunk Neon</strong>
                                        <span style="font-size: 11px; color: #646970;"><?php esc_html_e( 'Futuristic Tech Glow', 'netfett-maintenance' ); ?></span>
                                    </div>
                                </label>
                            </div>
                            <p class="description">
                                <?php esc_html_e( 'Choose the visual style of your maintenance page. Previews illustrate the background and container style.', 'netfett-maintenance' ); ?>
                            </p>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const cards = document.querySelectorAll('.netfett-template-card');
                                    cards.forEach(function(card) {
                                        card.addEventListener('click', function(e) {
                                            cards.forEach(function(c) {
                                                c.classList.remove('active');
                                                c.style.borderColor = '#c3c4c7';
                                            });
                                            this.classList.add('active');
                                            this.style.borderColor = '#2271b1';
                                            const radio = this.querySelector('input[type="radio"]');
                                            if (radio) {
                                                radio.checked = true;
                                            }
                                        });
                                    });
                                });
                            </script>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; font-size: 14px; padding: 20px 10px 20px 0;"><?php esc_html_e( 'Logo Image', 'netfett-maintenance' ); ?></th>
                        <td style="padding: 15px 10px;">
                            <?php 
                            $logo_url = get_option( 'netfett_maint_logo', '' );
                            ?>
                            <div id="netfett-logo-preview-wrapper" style="margin-bottom: 10px; max-width: 200px; <?php echo empty( $logo_url ) ? 'display: none;' : ''; ?>">
                                <img id="netfett-logo-preview" src="<?php echo esc_url( $logo_url ); ?>" style="max-width: 100%; height: auto; border: 1px solid #c3c4c7; padding: 5px; background: #f0f0f1; border-radius: 4px;" />
                            </div>
                            <input type="hidden" name="netfett_maint_logo" id="netfett-logo-url" value="<?php echo esc_attr( $logo_url ); ?>" />
                            <button type="button" class="button" id="netfett-logo-upload-button"><?php esc_html_e( 'Select Logo', 'netfett-maintenance' ); ?></button>
                            <button type="button" class="button button-link-delete" id="netfett-logo-remove-button" style="<?php echo empty( $logo_url ) ? 'display: none;' : ''; ?>"><?php esc_html_e( 'Remove Logo', 'netfett-maintenance' ); ?></button>
                            <p class="description"><?php esc_html_e( 'Upload or select a logo to be displayed above the title on the maintenance page.', 'netfett-maintenance' ); ?></p>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const uploadBtn = document.getElementById('netfett-logo-upload-button');
                                    const removeBtn = document.getElementById('netfett-logo-remove-button');
                                    const inputField = document.getElementById('netfett-logo-url');
                                    const previewImg = document.getElementById('netfett-logo-preview');
                                    const previewWrapper = document.getElementById('netfett-logo-preview-wrapper');
                                    
                                    let customUploader;
                                    
                                    if (uploadBtn) {
                                        uploadBtn.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            
                                            if (customUploader) {
                                                customUploader.open();
                                                return;
                                            }
                                            
                                            customUploader = wp.media({
                                                title: '<?php echo esc_js( __( 'Select Logo', 'netfett-maintenance' ) ); ?>',
                                                button: {
                                                    text: '<?php echo esc_js( __( 'Use this Logo', 'netfett-maintenance' ) ); ?>'
                                                },
                                                multiple: false
                                            });
                                            
                                            customUploader.on('select', function() {
                                                const attachment = customUploader.state().get('selection').first().toJSON();
                                                inputField.value = attachment.url;
                                                previewImg.src = attachment.url;
                                                previewWrapper.style.display = 'block';
                                                removeBtn.style.display = 'inline-block';
                                            });
                                            
                                            customUploader.open();
                                        });
                                    }
                                    
                                    if (removeBtn) {
                                        removeBtn.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            inputField.value = '';
                                            previewImg.src = '';
                                            previewWrapper.style.display = 'none';
                                            this.style.display = 'none';
                                        });
                                    }
                                });
                            </script>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; font-size: 14px; padding: 20px 10px 20px 0;"><?php esc_html_e( 'Estimated Completion (Countdown)', 'netfett-maintenance' ); ?></th>
                        <td style="padding: 15px 10px;">
                            <?php 
                            $countdown_val = get_option( 'netfett_maint_countdown', '' );
                            $auto_deactivate_val = get_option( 'netfett_maint_auto_deactivate', '0' );
                            ?>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="datetime-local" id="netfett-countdown-input" name="netfett_maint_countdown" value="<?php echo esc_attr( $countdown_val ); ?>" style="height: 35px; border-radius: 4px; border: 1px solid #c3c4c7; padding: 0 8px; font-family: inherit;" />
                                <button type="button" class="button" id="netfett-countdown-reset" style="height: 35px;"><?php esc_html_e( 'Reset', 'netfett-maintenance' ); ?></button>
                            </div>
                            <p class="description" style="margin-top: 5px;"><?php esc_html_e( 'Optional. Set a date and time when the maintenance is expected to end to show a live countdown.', 'netfett-maintenance' ); ?></p>
                            
                            <div style="margin-top: 15px;">
                                <label style="display: inline-flex; align-items: center; cursor: pointer;">
                                    <input type="checkbox" name="netfett_maint_auto_deactivate" value="1" <?php checked( $auto_deactivate_val, '1' ); ?> style="margin-right: 8px;" />
                                    <span><?php esc_html_e( 'Deactivate maintenance mode automatically when countdown ends', 'netfett-maintenance' ); ?></span>
                                </label>
                            </div>
                            
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const resetBtn = document.getElementById('netfett-countdown-reset');
                                    const inputField = document.getElementById('netfett-countdown-input');
                                    if (resetBtn && inputField) {
                                        resetBtn.addEventListener('click', function() {
                                            inputField.value = '';
                                        });
                                    }
                                });
                            </script>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; font-size: 14px; padding: 20px 10px 20px 0;"><?php esc_html_e( 'Maintenance Page Title', 'netfett-maintenance' ); ?></th>
                        <td style="padding: 15px 10px;">
                            <input type="text" name="netfett_maint_title" value="<?php echo esc_attr( get_option( 'netfett_maint_title', __( 'We will be back soon!', 'netfett-maintenance' ) ) ); ?>" class="regular-text" style="width: 100%; max-width: 500px; height: 35px; border-radius: 4px;" />
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; font-size: 14px; padding: 20px 10px 20px 0;"><?php esc_html_e( 'Maintenance Text / Message', 'netfett-maintenance' ); ?></th>
                        <td style="padding: 15px 10px;">
                            <textarea name="netfett_maint_message" rows="5" class="large-text" style="width: 100%; max-width: 500px; border-radius: 4px; font-family: inherit;"><?php echo esc_textarea( get_option( 'netfett_maint_message', __( 'Our website is currently undergoing maintenance. We are working on something great and will be back for you shortly.', 'netfett-maintenance' ) ) ); ?></textarea>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; font-size: 14px; padding: 20px 10px 20px 0;"><?php esc_html_e( 'Contact Email (optional)', 'netfett-maintenance' ); ?></th>
                        <td style="padding: 15px 10px;">
                            <input type="email" name="netfett_maint_email" value="<?php echo esc_attr( get_option( 'netfett_maint_email', get_option('admin_email') ) ); ?>" class="regular-text" style="width: 100%; max-width: 500px; height: 35px; border-radius: 4px;" />
                            <p class="description" style="margin-top: 5px;"><?php esc_html_e( 'Leave an email address for visitor inquiries.', 'netfett-maintenance' ); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2" style="padding: 10px 0;"><hr style="border: 0; border-top: 1px solid #dbdec2; border-color: #dcdcde; margin: 15px 0;" /></td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row" colspan="2" style="font-weight: 600; font-size: 16px; color: #1d2327; padding-bottom: 10px;"><?php esc_html_e( 'Social Media Links', 'netfett-maintenance' ); ?></th>
                    </tr>

                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; font-size: 14px; padding: 15px 10px 15px 0;"><?php esc_html_e( 'Instagram URL', 'netfett-maintenance' ); ?></th>
                        <td style="padding: 10px 10px;">
                            <input type="url" name="netfett_maint_social_instagram" value="<?php echo esc_url( get_option( 'netfett_maint_social_instagram' ) ); ?>" class="regular-text" placeholder="https://instagram.com/netfett" style="width: 100%; max-width: 500px; height: 35px; border-radius: 4px;" />
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; font-size: 14px; padding: 15px 10px 15px 0;"><?php esc_html_e( 'Facebook URL', 'netfett-maintenance' ); ?></th>
                        <td style="padding: 10px 10px;">
                            <input type="url" name="netfett_maint_social_facebook" value="<?php echo esc_url( get_option( 'netfett_maint_social_facebook' ) ); ?>" class="regular-text" placeholder="https://facebook.com/..." style="width: 100%; max-width: 500px; height: 35px; border-radius: 4px;" />
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; font-size: 14px; padding: 15px 10px 15px 0;"><?php esc_html_e( 'LinkedIn URL', 'netfett-maintenance' ); ?></th>
                        <td style="padding: 10px 10px;">
                            <input type="url" name="netfett_maint_social_linkedin" value="<?php echo esc_url( get_option( 'netfett_maint_social_linkedin' ) ); ?>" class="regular-text" placeholder="https://linkedin.com/in/..." style="width: 100%; max-width: 500px; height: 35px; border-radius: 4px;" />
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; font-size: 14px; padding: 15px 10px 15px 0;"><?php esc_html_e( 'Twitter / X URL', 'netfett-maintenance' ); ?></th>
                        <td style="padding: 10px 10px;">
                            <input type="url" name="netfett_maint_social_twitter" value="<?php echo esc_url( get_option( 'netfett_maint_social_twitter' ) ); ?>" class="regular-text" placeholder="https://twitter.com/..." style="width: 100%; max-width: 500px; height: 35px; border-radius: 4px;" />
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row" style="font-weight: 600; font-size: 14px; padding: 15px 10px 15px 0;"><?php esc_html_e( 'Uninstall Behavior', 'netfett-maintenance' ); ?></th>
                        <td style="padding: 10px 10px;">
                            <?php $delete_on_uninstall_val = get_option( 'netfett_maint_delete_on_uninstall', '0' ); ?>
                            <label style="display: inline-flex; align-items: center; cursor: pointer;">
                                <input type="checkbox" name="netfett_maint_delete_on_uninstall" value="1" <?php checked( $delete_on_uninstall_val, '1' ); ?> style="margin-right: 8px;" />
                                <span><?php esc_html_e( 'Delete all plugin settings and data when deleting the plugin', 'netfett-maintenance' ); ?></span>
                            </label>
                            <p class="description" style="margin-top: 5px;"><?php esc_html_e( 'Check this box to perform a clean uninstall. All maintenance settings and social links will be permanently deleted from the database when you delete the plugin.', 'netfett-maintenance' ); ?></p>
                        </td>
                    </tr>
                </table>
                
                <div style="margin-top: 25px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap; width: 100%;">
                    <?php submit_button( __( 'Save Settings', 'netfett-maintenance' ), 'primary', 'submit', false, array( 'style' => 'background: #2271b1; border-color: #2271b1; border-radius: 4px; padding: 8px 25px; font-size: 14px; height: auto; line-height: 1.4; font-weight: 600; margin: 0;' ) ); ?>
                    <a href="<?php echo esc_url( add_query_arg( array( 'preview_maintenance' => '1', 'preview_nonce' => wp_create_nonce( 'netfett_preview' ) ), home_url( '/' ) ) ); ?>" target="_blank" class="button button-secondary" style="height: 38px; line-height: 36px; padding: 0 20px; font-size: 14px; font-weight: 600; border-radius: 4px; display: inline-flex; align-items: center; box-sizing: border-box; text-decoration: none;">
                        <?php esc_html_e( 'Preview Page', 'netfett-maintenance' ); ?>
                    </a>
                    
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'options-general.php?page=netfett-maintenance&action=reset_defaults' ), 'netfett_reset_defaults' ) ); ?>" class="button button-secondary netfett-btn-danger" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to reset all settings to their default values? This action cannot be undone.', 'netfett-maintenance' ) ); ?>');" style="margin-left: auto; border-color: #b32d2e; color: #b32d2e; background: #fff; height: 38px; line-height: 36px; padding: 0 20px; font-size: 14px; font-weight: 600; border-radius: 4px; display: inline-flex; align-items: center; box-sizing: border-box; text-decoration: none; transition: all 0.15s ease-in-out;">
                        <?php esc_html_e( 'Reset to Defaults', 'netfett-maintenance' ); ?>
                    </a>
                </div>
            </form>

            <style>
                /* Switch Styling */
                .netfett-switch {
                    position: relative;
                    display: inline-block;
                    width: 50px;
                    height: 26px;
                }
                .netfett-switch input { 
                    opacity: 0;
                    width: 0;
                    height: 0;
                }
                .netfett-slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    -webkit-transition: .4s;
                    transition: .4s;
                    border-radius: 34px;
                }
                .netfett-slider:before {
                    position: absolute;
                    content: "";
                    height: 18px;
                    width: 18px;
                    left: 4px;
                    bottom: 4px;
                    background-color: white;
                    -webkit-transition: .4s;
                    transition: .4s;
                    border-radius: 50%;
                }
                input:checked + .netfett-slider {
                    background-color: #46b450;
                }
                input:focus + .netfett-slider {
                    box-shadow: 0 0 1px #46b450;
                }
                input:checked + .netfett-slider:before {
                    -webkit-transform: translateX(24px);
                    -ms-transform: translateX(24px);
                    transform: translateX(24px);
                }
                
                /* Visual Template Selection Card Styling */
                .netfett-template-card {
                    transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s !important;
                }
                .netfett-template-card:hover {
                    border-color: #2271b1 !important;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                }
                .netfett-template-card.active {
                    border-color: #2271b1 !important;
                    box-shadow: 0 0 0 1px #2271b1, 0 4px 12px rgba(34,113,177,0.15) !important;
                }
                
                /* Micro-animations inside preview */
                @keyframes float-mini {
                    0% { transform: translate(0, 0); }
                    100% { transform: translate(4px, 4px); }
                }
                .netfett-template-card:hover .mini-blob.blob1 {
                    animation: float-mini 3s infinite alternate ease-in-out;
                }
                .netfett-template-card:hover .mini-blob.blob2 {
                    animation: float-mini 3.5s infinite alternate-reverse ease-in-out;
                }
                /* Destructive Reset Button Hover Effect */
                .netfett-btn-danger:hover {
                    background: #b32d2e !important;
                    color: #fff !important;
                    border-color: #b32d2e !important;
                }
            </style>
        </div>
        <?php
    }

    /**
     * Add a node to the admin bar showing if maintenance mode is active.
     */
    public function admin_bar_indicator( $wp_admin_bar ) {
        if ( ! $this->is_maintenance_active() ) {
            return;
        }

        $args = array(
            'id'    => 'netfett_maint_indicator',
            'title' => '<span class="ab-icon dashicons dashicons-admin-tools" style="top:2px;"></span><span style="color:#ffb900; font-weight:bold;">' . esc_html__( 'Maintenance Mode ACTIVE', 'netfett-maintenance' ) . '</span>',
            'href'  => admin_url( 'options-general.php?page=netfett-maintenance' ),
            'meta'  => array(
                'title' => esc_html__( 'Netfett Maintenance Settings', 'netfett-maintenance' ),
            )
        );
        $wp_admin_bar->add_node( $args );
    }

    /**
     * Add an admin notice to notify standard admin screens when maintenance is active.
     */
    public function admin_notice_indicator() {
        if ( ! $this->is_maintenance_active() ) {
            return;
        }
        
        $screen = get_current_screen();
        if ( $screen && $screen->id === 'settings_page_netfett-maintenance' ) {
            return;
        }
        
        ?>
        <div class="notice notice-warning is-dismissible" style="border-left-color: #ffb900;">
            <p>
                <strong><?php esc_html_e( 'Netfett Maintenance:', 'netfett-maintenance' ); ?></strong> <?php esc_html_e( 'Maintenance mode is currently active. Visitors see the maintenance page.', 'netfett-maintenance' ); ?>
                <a style="margin-left: 10px; font-weight: 600;" href="<?php echo esc_url( admin_url( 'options-general.php?page=netfett-maintenance' ) ); ?>"><?php esc_html_e( 'View settings', 'netfett-maintenance' ); ?></a>.
            </p>
        </div>
        <?php
    }

    /**
     * Retrieves the current bypass token from the database.
     * Automatically generates a new token if none exists yet.
     *
     * @return string The bypass token.
     */
    private function get_bypass_token() {
        $token = get_option( 'my_maintenance_bypass_token' );
        if ( empty( $token ) ) {
            $token = $this->generate_and_save_bypass_token();
        }
        return $token;
    }

    /**
     * Generates a new, secure and random bypass token and stores it.
     *
     * @return string The newly generated token.
     */
    private function generate_and_save_bypass_token() {
        // wp_generate_password( 20, false, false ) generates a 20-character alphanumeric token (without special characters)
        $token = wp_generate_password( 20, false, false );
        update_option( 'my_maintenance_bypass_token', $token );
        return $token;
    }

    /**
     * Handles regenerating the token in the WordPress backend.
     */
    public function handle_regenerate_bypass_token() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
        if ( 'regenerate_bypass' === $action ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'netfett-maintenance' ) );
            }
            
            // Nonce safety check
            check_admin_referer( 'netfett_regenerate_bypass' );
            
            $this->generate_and_save_bypass_token();
            
            // Redirect to settings page with success message
            wp_safe_redirect( admin_url( 'options-general.php?page=netfett-maintenance&settings-updated=true' ) );
            exit;
        }
    }

    /**
     * Detects the bypass link in the frontend and sets the cookie.
     */
    public function check_bypass_link() {
        if ( is_admin() ) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $bypass_param = isset( $_GET['bypass'] ) ? sanitize_text_field( wp_unslash( $_GET['bypass'] ) ) : '';
        if ( ! empty( $bypass_param ) ) {
            $token = $this->get_bypass_token();
            
            // Time-constant comparison to prevent timing attacks
            if ( ! empty( $token ) && hash_equals( $token, $bypass_param ) ) {
                $cookie_name  = 'my_maintenance_bypass';
                $cookie_value = $token;
                $expiry       = time() + ( 7 * DAY_IN_SECONDS ); // Valid for 7 days
                
                $secure   = is_ssl();
                $httponly = true;
                
                // Set cookie (considering SameSite standards as of PHP 7.3)
                if ( PHP_VERSION_ID >= 70300 ) {
                    setcookie( $cookie_name, $cookie_value, array(
                        'expires'  => $expiry,
                        'path'     => COOKIEPATH ? COOKIEPATH : '/',
                        'domain'   => COOKIE_DOMAIN,
                        'secure'   => $secure,
                        'httponly' => $httponly,
                        'samesite' => 'Lax',
                    ) );
                } else {
                    setcookie( $cookie_name, $cookie_value, $expiry, ( COOKIEPATH ? COOKIEPATH : '/' ) . '; SameSite=Lax', COOKIE_DOMAIN, $secure, $httponly );
                }
                
                // Clean URL: remove the bypass parameter
                $redirect_url = remove_query_arg( 'bypass' );
                wp_safe_redirect( $redirect_url );
                exit;
            }
        }
    }

    /**
     * Resets all plugin settings to their default values.
     */
    public function handle_reset_defaults(): void {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
        if ( 'reset_defaults' === $action ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'You do not have sufficient permissions to perform this action.', 'netfett-maintenance' ) );
            }
            
            check_admin_referer( 'netfett_reset_defaults' );
            
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
            
            // Redirect with a success parameter
            wp_safe_redirect( admin_url( 'options-general.php?page=netfett-maintenance&settings-reset=true' ) );
            exit;
        }
    }

    /**
     * Checks if a valid bypass cookie is present and active.
     *
     * @return bool True if the bypass is active, false otherwise.
     */
    public function is_bypass_active() {
        $cookie_name = 'my_maintenance_bypass';
        if ( isset( $_COOKIE[ $cookie_name ] ) ) {
            $token = get_option( 'my_maintenance_bypass_token' );
            $cookie_token = sanitize_text_field( wp_unslash( $_COOKIE[ $cookie_name ] ) );
            // The cookie is only valid if it matches the current token in the database
            if ( ! empty( $token ) && hash_equals( $token, $cookie_token ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add direct Settings link to the plugins page action links.
     *
     * @param array $links Array of action links.
     * @return array Modified array of action links.
     */
    public function add_plugin_action_links( $links ) {
        $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=netfett-maintenance' ) ) . '">' . esc_html__( 'Settings', 'netfett-maintenance' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Handles redirecting to the settings page upon plugin activation (fallback).
     */
    public function handle_activation_redirect() {
        if ( get_option( 'netfett_maint_activation_redirect' ) ) {
            delete_option( 'netfett_maint_activation_redirect' );

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) {
                return;
            }

            wp_redirect( admin_url( 'options-general.php?page=netfett-maintenance' ) );
            exit;
        }
    }
}

new Netfett_Maintenance();

register_activation_hook( __FILE__, function() {
    update_option( 'netfett_maint_activation_redirect', 1 );
} );

// Immediate Welcome Redirect upon plugin activation (with Windows path normalization)
add_action( 'activated_plugin', function( $plugin, $network_wide = false ) {
    $current = plugin_basename( __FILE__ );
    if ( function_exists( 'wp_normalize_path' ) ) {
        $plugin = wp_normalize_path( $plugin );
        $current = wp_normalize_path( $current );
    }
    if ( $plugin === $current && ! $network_wide && ! is_network_admin() ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( ! isset( $_GET['activate-multi'] ) && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            wp_redirect( admin_url( 'options-general.php?page=netfett-maintenance' ) );
            exit;
        }
    }
}, 10, 2 );
