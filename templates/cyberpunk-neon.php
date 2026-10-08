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
            --bg-color: #05050a;
            --grid-color: rgba(0, 240, 255, 0.04);
            --text-primary: #00ffff;
            --text-muted: #8ab4f8;
            --text-white: #ffffff;
            --neon-cyan: #00ffff;
            --neon-magenta: #ff007f;
            --card-bg: rgba(5, 5, 10, 0.85);
            --card-border: #00ffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Consolas, "Andale Mono WT", "Andale Mono", "Lucida Console", Monaco, "Courier New", monospace;
            background-color: var(--bg-color);
            background-image: 
                linear-gradient(var(--grid-color) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-color) 1px, transparent 1px);
            background-size: 30px 30px;
            background-position: center;
            color: var(--text-white);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }

        /* Scanline Overlay Effect */
        body::before {
            content: " ";
            display: block;
            position: absolute;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.06), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.06));
            z-index: 99;
            background-size: 100% 4px, 6px 100%;
            pointer-events: none;
        }

        /* Cyberpunk Card Container */
        .cyber-container {
            width: 100%;
            max-width: 580px;
            background: var(--card-bg);
            border: 2px solid var(--neon-cyan);
            border-radius: 0px;
            padding: 50px 40px;
            position: relative;
            box-shadow: 
                0 0 15px rgba(0, 240, 255, 0.2),
                inset 0 0 15px rgba(0, 240, 255, 0.1);
            text-align: center;
            animation: cyber-load 0.6s cubic-bezier(0.15, 0.85, 0.45, 1) forwards;
        }

        /* Cyber Corners Decorative Details */
        .cyber-container::after {
            content: "SYSTEM_OFFLINE";
            position: absolute;
            top: -12px;
            right: 20px;
            background: var(--bg-color);
            padding: 0 10px;
            font-size: 0.65rem;
            color: var(--neon-magenta);
            border: 1px solid var(--neon-magenta);
            letter-spacing: 2px;
        }

        .cyber-container::before {
            content: "";
            position: absolute;
            bottom: -2px;
            left: -2px;
            width: 20px;
            height: 20px;
            border-left: 4px solid var(--neon-magenta);
            border-bottom: 4px solid var(--neon-magenta);
            pointer-events: none;
        }

        .cyber-deco-corner {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 20px;
            height: 20px;
            border-right: 4px solid var(--neon-magenta);
            border-top: 4px solid var(--neon-magenta);
            pointer-events: none;
        }

        @keyframes cyber-load {
            0% {
                opacity: 0;
                transform: scale(0.95) rotate(-0.5deg);
                box-shadow: 0 0 0px rgba(0, 240, 255, 0);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }

        /* Pulsing Status */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan);
            background: rgba(0, 240, 255, 0.05);
            padding: 6px 16px;
            font-size: 0.7rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 35px;
            box-shadow: 0 0 8px rgba(0, 240, 255, 0.1);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: var(--neon-cyan);
            box-shadow: 0 0 8px var(--neon-cyan);
            animation: cyber-blink 1s infinite alternate;
        }

        @keyframes cyber-blink {
            0% { opacity: 0.3; }
            100% { opacity: 1; }
        }

        /* Typography - Futuristic Tech Vibe */
        h1 {
            font-size: 1.8rem;
            font-weight: 900;
            line-height: 1.3;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-white);
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.5), 0 0 10px var(--neon-magenta);
        }

        p.message {
            font-size: 0.88rem;
            line-height: 1.7;
            color: var(--text-muted);
            margin-bottom: 40px;
            text-align: left;
            border-left: 3px solid var(--neon-magenta);
            padding-left: 15px;
            background: rgba(255, 0, 127, 0.02);
            padding-top: 10px;
            padding-bottom: 10px;
        }

        /* Neon Action Button */
        .contact-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: transparent;
            border: 1px solid var(--neon-magenta);
            color: var(--neon-magenta);
            padding: 14px 28px;
            font-size: 0.8rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 0 10px rgba(255, 0, 127, 0.1);
        }

        .contact-btn:hover {
            background: var(--neon-magenta);
            color: var(--bg-color);
            box-shadow: 0 0 20px var(--neon-magenta);
            text-shadow: none;
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
            background: repeating-linear-gradient(90deg, var(--neon-cyan) 0, var(--neon-cyan) 4px, transparent 0, transparent 8px);
            margin: 40px 0;
            opacity: 0.4;
        }

        /* Social Links */
        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan);
            transition: all 0.3s ease;
            text-decoration: none;
            background: rgba(0, 240, 255, 0.02);
        }

        .social-link:hover {
            background: var(--neon-cyan);
            color: var(--bg-color);
            box-shadow: 0 0 15px var(--neon-cyan);
        }

        .social-link svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
        }

        /* Footer */
        .footer-note {
            font-size: 0.68rem;
            color: rgba(255, 255, 255, 0.3);
            margin-top: 40px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .footer-note a {
            color: var(--neon-cyan);
            text-decoration: none;
            border-bottom: 1px dotted var(--neon-cyan);
        }

        .footer-note a:hover {
            color: var(--text-white);
            border-bottom: 1px solid var(--text-white);
        }

        @media (max-width: 480px) {
            .cyber-container {
                padding: 40px 20px;
            }
            h1 {
                font-size: 1.4rem;
            }
        }
    </style>
</head>
<body>

    <div class="cyber-container">
        <div class="cyber-deco-corner"></div>
        
        <!-- Status -->
        <div class="status-badge">
            <span class="status-dot"></span>
            <?php esc_html_e( 'System.Maintenance', 'netfett-maintenance' ); ?>
        </div>

        <!-- Logo -->
        <?php 
        $netfett_logo_url = get_option( 'netfett_maint_logo' );
        if ( ! empty( $netfett_logo_url ) ) : ?>
            <div class="maintenance-logo-wrap" style="margin-bottom: 30px;">
                <img src="<?php echo esc_url( $netfett_logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> Logo" style="max-width: 100%; max-height: 80px; height: auto; border: 1px solid var(--neon-cyan); padding: 5px; box-shadow: 0 0 10px rgba(0, 240, 255, 0.15);" />
            </div>
        <?php endif; ?>

        <!-- Headings -->
        <h1>// <?php echo esc_html( $netfett_title ); ?></h1>
        <p class="message">> <?php echo wp_kses_post( $netfett_message ); ?></p>

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
                <div class="countdown-item" style="background: rgba(5, 5, 10, 0.9); border: 1px solid var(--neon-cyan); padding: 10px 15px; min-width: 70px; box-shadow: 0 0 8px rgba(0, 240, 255, 0.1);">
                    <span id="countdown-days" style="display: block; font-size: 1.5rem; font-weight: bold; color: var(--neon-cyan); text-shadow: 0 0 5px var(--neon-cyan);">00</span>
                    <span style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;"><?php esc_html_e( 'Days', 'netfett-maintenance' ); ?></span>
                </div>
                <div class="countdown-item" style="background: rgba(5, 5, 10, 0.9); border: 1px solid var(--neon-cyan); padding: 10px 15px; min-width: 70px; box-shadow: 0 0 8px rgba(0, 240, 255, 0.1);">
                    <span id="countdown-hours" style="display: block; font-size: 1.5rem; font-weight: bold; color: var(--neon-cyan); text-shadow: 0 0 5px var(--neon-cyan);">00</span>
                    <span style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;"><?php esc_html_e( 'Hours', 'netfett-maintenance' ); ?></span>
                </div>
                <div class="countdown-item" style="background: rgba(5, 5, 10, 0.9); border: 1px solid var(--neon-cyan); padding: 10px 15px; min-width: 70px; box-shadow: 0 0 8px rgba(0, 240, 255, 0.1);">
                    <span id="countdown-minutes" style="display: block; font-size: 1.5rem; font-weight: bold; color: var(--neon-cyan); text-shadow: 0 0 5px var(--neon-cyan);">00</span>
                    <span style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;"><?php esc_html_e( 'Minutes', 'netfett-maintenance' ); ?></span>
                </div>
                <div class="countdown-item" style="background: rgba(5, 5, 10, 0.9); border: 1px solid var(--neon-cyan); padding: 10px 15px; min-width: 70px; box-shadow: 0 0 8px rgba(0, 240, 255, 0.1);">
                    <span id="countdown-seconds" style="display: block; font-size: 1.5rem; font-weight: bold; color: var(--neon-cyan); text-shadow: 0 0 5px var(--neon-cyan);">00</span>
                    <span style="font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;"><?php esc_html_e( 'Seconds', 'netfett-maintenance' ); ?></span>
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
                    <rect x="2" y="4" width="20" height="16" rx="2" />
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                </svg>
                <?php esc_html_e( 'Establish Contact', 'netfett-maintenance' ); ?>
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
