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
            --bg-color-1: #0b0f19;
            --bg-color-2: #111827;
            --bg-color-3: #1e1b4b;
            --accent-1: #6366f1;
            --accent-2: #d946ef;
            --accent-3: #f43f5e;
            --text-primary: #ffffff;
            --text-secondary: #94a3b8;
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.08);
            --card-shadow: rgba(0, 0, 0, 0.4);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            background: radial-gradient(circle at 50% 50%, var(--bg-color-3) 0%, var(--bg-color-2) 50%, var(--bg-color-1) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            padding: 20px;
        }

        /* Dynamic Background Blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.18;
            z-index: 1;
            pointer-events: none;
        }

        .blob-1 {
            width: 500px;
            height: 500px;
            background: var(--accent-1);
            top: -10%;
            left: -10%;
            animation: float-blob 25s infinite alternate ease-in-out;
        }

        .blob-2 {
            width: 600px;
            height: 600px;
            background: var(--accent-2);
            bottom: -15%;
            right: -10%;
            animation: float-blob 30s infinite alternate-reverse ease-in-out;
        }

        @keyframes float-blob {
            0% {
                transform: translate(0, 0) scale(1);
            }
            50% {
                transform: translate(50px, 80px) scale(1.1);
            }
            100% {
                transform: translate(-30px, -40px) scale(0.95);
            }
        }

        /* Glassmorphic Container Card */
        .maintenance-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            box-shadow: 0 24px 80px var(--card-shadow);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 32px;
            padding: 50px 40px;
            width: 100%;
            max-width: 580px;
            text-align: center;
            z-index: 10;
            position: relative;
            transform: translateY(0);
            animation: card-appear 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes card-appear {
            0% {
                opacity: 0;
                transform: translateY(40px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Pulsing Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399;
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 25px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            position: relative;
        }

        .status-dot::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #10b981;
            border-radius: 50%;
            animation: pulse-dot 1.8s infinite ease-in-out;
        }

        @keyframes pulse-dot {
            0% {
                transform: scale(1);
                opacity: 0.8;
            }
            100% {
                transform: scale(3);
                opacity: 0;
            }
        }

        /* Maintenance Logo/Icon */
        .maintenance-icon-wrap {
            margin-bottom: 25px;
            display: inline-block;
            position: relative;
        }

        .maintenance-icon {
            width: 80px;
            height: 80px;
            stroke: url(#iconGradient);
            animation: rotate-gear 20s linear infinite;
        }

        @keyframes rotate-gear {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Typography */
        h1 {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 18px;
            background: linear-gradient(135deg, #ffffff 30%, #a5b4fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.02em;
        }

        p.message {
            color: var(--text-secondary);
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 35px;
            font-weight: 300;
        }

        /* Divider */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0) 100%);
            margin: 30px 0;
        }

        /* Contact Button */
        .contact-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-primary);
            padding: 12px 24px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .contact-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .contact-btn svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
        }

        /* Social Links */
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
        }

        .social-link {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            color: var(--text-secondary);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
        }

        .social-link:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-3px) scale(1.05);
        }

        .social-link svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        /* Footer Notes */
        .footer-note {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.2);
            margin-top: 30px;
            letter-spacing: 0.05em;
        }
        
        .footer-note a {
            color: rgba(255, 255, 255, 0.3);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .footer-note a:hover {
            color: var(--text-primary);
        }

        /* Mobile Adjustments */
        @media (max-width: 480px) {
            .maintenance-card {
                padding: 40px 24px;
            }
            h1 {
                font-size: 1.8rem;
            }
            p.message {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <!-- Main Card -->
    <div class="maintenance-card">
        
        <!-- Status -->
        <div class="status-badge">
            <span class="status-dot"></span>
            <?php esc_html_e( 'Maintenance Mode', 'netfett-maintenance' ); ?>
        </div>
        
        <!-- Logo / Icon -->
        <?php 
        $netfett_logo_url = get_option( 'netfett_maint_logo' );
        if ( ! empty( $netfett_logo_url ) ) : ?>
            <div class="maintenance-logo-wrap" style="margin-bottom: 25px;">
                <img src="<?php echo esc_url( $netfett_logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> Logo" style="max-width: 100%; max-height: 80px; height: auto;" />
            </div>
        <?php else : ?>
            <div class="maintenance-icon-wrap">
                <svg class="maintenance-icon" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <defs>
                        <linearGradient id="iconGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#818cf8" />
                            <stop offset="100%" stop-color="#ec4899" />
                        </linearGradient>
                    </defs>
                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.1a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                    <circle cx="12" cy="12" r="3" />
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
            <div class="netfett-countdown-container" id="netfett-countdown" data-timestamp="<?php echo esc_attr( $netfett_countdown_timestamp ); ?>" style="display: flex; justify-content: center; gap: 15px; margin-bottom: 35px;">
                <div class="countdown-item" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 12px; min-width: 70px; backdrop-filter: blur(10px);">
                    <span id="countdown-days" style="display: block; font-size: 1.8rem; font-weight: 800; color: #ffffff;">00</span>
                    <span style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e( 'Days', 'netfett-maintenance' ); ?></span>
                </div>
                <div class="countdown-item" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 12px; min-width: 70px; backdrop-filter: blur(10px);">
                    <span id="countdown-hours" style="display: block; font-size: 1.8rem; font-weight: 800; color: #ffffff;">00</span>
                    <span style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e( 'Hours', 'netfett-maintenance' ); ?></span>
                </div>
                <div class="countdown-item" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 12px; min-width: 70px; backdrop-filter: blur(10px);">
                    <span id="countdown-minutes" style="display: block; font-size: 1.8rem; font-weight: 800; color: #ffffff;">00</span>
                    <span style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e( 'Minutes', 'netfett-maintenance' ); ?></span>
                </div>
                <div class="countdown-item" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 12px; min-width: 70px; backdrop-filter: blur(10px);">
                    <span id="countdown-seconds" style="display: block; font-size: 1.8rem; font-weight: 800; color: #ffffff;">00</span>
                    <span style="font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;"><?php esc_html_e( 'Seconds', 'netfett-maintenance' ); ?></span>
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

        <!-- Dynamic Action / Contact -->
        <?php if ( ! empty( $netfett_email ) ) : ?>
            <a href="mailto:<?php echo esc_attr( $netfett_email ); ?>" class="contact-btn">
                <svg viewBox="0 0 24 24">
                    <rect x="2" y="4" width="20" height="16" rx="2" />
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                </svg>
                <?php esc_html_e( 'Contact Us', 'netfett-maintenance' ); ?>
            </a>
        <?php endif; ?>

        <!-- Social Media Section -->
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
                        <!-- Modern X logo svg -->
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
