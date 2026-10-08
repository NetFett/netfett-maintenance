<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Fetch plugin options with default fallbacks using plugin prefixes to satisfy WPCS
$netfett_title     = get_option( 'netfett_maint_title', __( 'We will be back soon!', 'netfett-maintenance' ) );
$netfett_message   = get_option( 'netfett_maint_message', __( 'Our website is currently undergoing maintenance. We are working on something great and will be back for you shortly.', 'netfett-maintenance' ) );
$netfett_email     = get_option( 'netfett_maint_email' );
$netfett_instagram = get_option( 'netfett_maint_social_instagram' );
$netfett_facebook  = get_option( 'netfett_maint_social_facebook' );
$netfett_linkedin  = get_option( 'netfett_maint_social_linkedin' );
$netfett_twitter   = get_option( 'netfett_maint_social_twitter' );

// Clean inputs
$netfett_title     = esc_html( $netfett_title );
$netfett_message   = nl2br( esc_html( $netfett_message ) );
$netfett_email     = sanitize_email( $netfett_email );
$netfett_instagram = esc_url( $netfett_instagram );
$netfett_facebook  = esc_url( $netfett_facebook );
$netfett_linkedin  = esc_url( $netfett_linkedin );
$netfett_twitter   = esc_url( $netfett_twitter );
?>
<!DOCTYPE html>
<html lang="<?php bloginfo('language'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( $netfett_title ); ?> - <?php bloginfo('name'); ?></title>
    
    <style>
        :root {
            --bg-color: #f8fafc;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --border-color: #e2e8f0;
            --accent-color: #0f172a;
            --accent-hover: #334155;
            --status-green: #10b981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .maintenance-container {
            width: 100%;
            max-width: 540px;
            padding: 60px 40px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.02);
            border-radius: 4px;
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Minimal Status Indicator */
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--text-secondary);
            margin-bottom: 35px;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            background-color: var(--status-green);
            border-radius: 50%;
        }

        /* Logo / Minimal Icon */
        .logo-wrap {
            margin-bottom: 30px;
            color: var(--text-primary);
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            stroke: currentColor;
            stroke-width: 1.25;
            fill: none;
        }

        /* Typography - Nordic Editorial Vibe */
        h1 {
            font-family: -apple-system-ui-serif, ui-serif, Georgia, Cambria, "Times New Roman", Times, serif;
            font-size: 2.25rem;
            font-weight: 400;
            line-height: 1.2;
            margin-bottom: 20px;
            letter-spacing: -0.01em;
            color: var(--text-primary);
        }

        p.message {
            font-size: 0.95rem;
            line-height: 1.6;
            color: var(--text-secondary);
            margin-bottom: 40px;
            font-weight: 350;
        }

        /* Minimal Contact Button */
        .contact-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent-color);
            color: #ffffff;
            padding: 12px 30px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            text-decoration: none;
            font-weight: 600;
            border-radius: 2px;
            transition: background-color 0.2s ease-in-out;
        }

        .contact-btn:hover {
            background-color: var(--accent-hover);
        }

        .contact-btn svg {
            width: 14px;
            height: 14px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
        }

        .divider {
            height: 1px;
            background-color: var(--border-color);
            margin: 40px 0;
        }

        /* Social Icons - Clean & Gray */
        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .social-link {
            color: var(--text-secondary);
            transition: color 0.2s ease-in-out;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .social-link:hover {
            color: var(--text-primary);
        }

        .social-link svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        /* Footer */
        .footer-note {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 40px;
            font-weight: 400;
            letter-spacing: 0.02em;
        }

        .footer-note a {
            color: var(--text-secondary);
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .footer-note a:hover {
            color: var(--text-primary);
        }

        @media (max-width: 480px) {
            .maintenance-container {
                padding: 40px 20px;
            }
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>

    <div class="maintenance-container">
        
        <!-- Status -->
        <div class="status-indicator">
            <span class="status-dot"></span>
            <?php esc_html_e( 'Maintenance Active', 'netfett-maintenance' ); ?>
        </div>

        <!-- Minimal Icon / Logo -->
        <?php 
        $netfett_logo_url = get_option( 'netfett_maint_logo' );
        if ( ! empty( $netfett_logo_url ) ) : ?>
            <div class="logo-wrap" style="margin-bottom: 30px;">
                <img src="<?php echo esc_url( $netfett_logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> Logo" style="max-width: 100%; max-height: 80px; height: auto;" />
            </div>
        <?php else : ?>
            <div class="logo-wrap">
                <svg class="logo-icon" viewBox="0 0 24 24" stroke="currentColor">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
            </div>
        <?php endif; ?>

        <!-- Headings -->
        <h1><?php echo esc_html( $netfett_title ); ?></h1>
        <p class="message"><?php echo wp_kses_post( $netfett_message ); ?></p>

        <!-- Countdown Timer -->
        <?php 
        $netfett_countdown_target = get_option( 'netfett_maint_countdown' );
        $netfett_countdown_timestamp = 0;
        if ( ! empty( $netfett_countdown_target ) ) {
            try {
                $netfett_timezone = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( get_option( 'timezone_string' ) ?: 'UTC' );
                $netfett_date = new DateTime( $netfett_countdown_target, $netfett_timezone );
                $netfett_countdown_timestamp = $netfett_date->getTimestamp();
            } catch ( Exception $e ) {
                $netfett_countdown_timestamp = strtotime( $netfett_countdown_target );
            }
        }
        if ( $netfett_countdown_timestamp > time() ) : ?>
            <div class="netfett-countdown-container" id="netfett-countdown" data-timestamp="<?php echo esc_attr( $netfett_countdown_timestamp ); ?>" style="display: flex; justify-content: center; gap: 15px; margin-bottom: 40px;">
                <div class="countdown-item" style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 4px; padding: 10px 15px; min-width: 65px;">
                    <span id="countdown-days" style="display: block; font-size: 1.5rem; font-weight: 600; color: var(--text-primary);">00</span>
                    <span style="font-size: 0.65rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.08em;"><?php esc_html_e( 'Days', 'netfett-maintenance' ); ?></span>
                </div>
                <div class="countdown-item" style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 4px; padding: 10px 15px; min-width: 65px;">
                    <span id="countdown-hours" style="display: block; font-size: 1.5rem; font-weight: 600; color: var(--text-primary);">00</span>
                    <span style="font-size: 0.65rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.08em;"><?php esc_html_e( 'Hours', 'netfett-maintenance' ); ?></span>
                </div>
                <div class="countdown-item" style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 4px; padding: 10px 15px; min-width: 65px;">
                    <span id="countdown-minutes" style="display: block; font-size: 1.5rem; font-weight: 600; color: var(--text-primary);">00</span>
                    <span style="font-size: 0.65rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.08em;"><?php esc_html_e( 'Minutes', 'netfett-maintenance' ); ?></span>
                </div>
                <div class="countdown-item" style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 4px; padding: 10px 15px; min-width: 65px;">
                    <span id="countdown-seconds" style="display: block; font-size: 1.5rem; font-weight: 600; color: var(--text-primary);">00</span>
                    <span style="font-size: 0.65rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.08em;"><?php esc_html_e( 'Seconds', 'netfett-maintenance' ); ?></span>
                </div>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const countdownEl = document.getElementById('netfett-countdown');
                    if (!countdownEl) return;
                    
                    const targetTime = parseInt(countdownEl.getAttribute('data-timestamp')) * 1000;
                    
                    const daysVal = document.getElementById('countdown-days');
                    const hoursVal = document.getElementById('countdown-hours');
                    const minutesVal = document.getElementById('countdown-minutes');
                    const secondsVal = document.getElementById('countdown-seconds');
                    
                    function updateTimer() {
                        const now = new Date().getTime();
                        const distance = targetTime - now;
                        
                        if (distance < 0) {
                            clearInterval(interval);
                            window.location.reload();
                            return;
                        }
                        
                        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                        
                        daysVal.innerText = String(days).padStart(2, '0');
                        hoursVal.innerText = String(hours).padStart(2, '0');
                        minutesVal.innerText = String(minutes).padStart(2, '0');
                        secondsVal.innerText = String(seconds).padStart(2, '0');
                    }
                    
                    updateTimer();
                    const interval = setInterval(updateTimer, 1000);
                });
            </script>
        <?php endif; ?>

        <!-- Contact Button -->
        <?php if ( ! empty( $netfett_email ) ) : ?>
            <a href="mailto:<?php echo esc_attr( $netfett_email ); ?>" class="contact-btn">
                <svg viewBox="0 0 24 24">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                    <polyline points="22,6 12,13 2,6" />
                </svg>
                <?php esc_html_e( 'Contact Us', 'netfett-maintenance' ); ?>
            </a>
        <?php endif; ?>

        <!-- Social Section -->
        <?php if ( ! empty( $netfett_instagram ) || ! empty( $netfett_facebook ) || ! empty( $netfett_linkedin ) || ! empty( $netfett_twitter ) ) : ?>
            <div class="divider"></div>
            <div class="social-links">
                <?php if ( ! empty( $netfett_instagram ) ) : ?>
                    <a href="<?php echo esc_url( $netfett_instagram ); ?>" class="social-link" target="_blank" rel="noopener noreferrer" title="Instagram">
                        <svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.002 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
                    </a>
                <?php endif; ?>
                <?php if ( ! empty( $netfett_facebook ) ) : ?>
                    <a href="<?php echo esc_url( $netfett_facebook ); ?>" class="social-link" target="_blank" rel="noopener noreferrer" title="Facebook">
                        <svg viewBox="0 0 24 24"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c4.56-.93 8-4.96 8-9.75z"/></svg>
                    </a>
                <?php endif; ?>
                <?php if ( ! empty( $netfett_linkedin ) ) : ?>
                    <a href="<?php echo esc_url( $netfett_linkedin ); ?>" class="social-link" target="_blank" rel="noopener noreferrer" title="LinkedIn">
                        <svg viewBox="0 0 24 24"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg>
                    </a>
                <?php endif; ?>
                <?php if ( ! empty( $netfett_twitter ) ) : ?>
                    <a href="<?php echo esc_url( $netfett_twitter ); ?>" class="social-link" target="_blank" rel="noopener noreferrer" title="Twitter / X">
                        <svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <p class="footer-note">
            &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. Admin? <a href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Log in here', 'netfett-maintenance' ); ?></a>.
        </p>

    </div>

</body>
</html>
