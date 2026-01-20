<?php
/**
 * Family Dashboard Login
 * Custom login page for parents to access the family dashboard
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Family Dashboard Login Shortcode
 */
function wcb_family_login_shortcode($atts) {
    $atts = shortcode_atts([
        'redirect' => home_url('/family-dashboard/'),
    ], $atts);

    // If already logged in, redirect to family dashboard
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        ob_start();
        ?>
        <div class="wcb-family-login">
            <div class="already-logged-in">
                <div class="logged-in-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="#27ae60">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <h3>You're already logged in!</h3>
                <p>Welcome back, <strong><?php echo esc_html($current_user->display_name); ?></strong></p>
                <a href="<?php echo esc_url($atts['redirect']); ?>" class="wcb-btn wcb-btn-primary">
                    Go to Family Dashboard
                </a>
            </div>
        </div>
        <?php
        wcb_family_login_styles();
        return ob_get_clean();
    }

    // Check for login errors
    $login_error = '';
    if (isset($_GET['login']) && $_GET['login'] === 'failed') {
        $login_error = 'Invalid username or password. Please try again.';
    }

    ob_start();
    ?>
    <div class="wcb-family-login">
        <!-- Info Section -->
        <div class="login-info-section">
            <div class="info-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="#007bff">
                    <path d="M16.5 12c1.38 0 2.49-1.12 2.49-2.5S17.88 7 16.5 7C15.12 7 14 8.12 14 9.5s1.12 2.5 2.5 2.5zM9 11c1.66 0 2.99-1.34 2.99-3S10.66 5 9 5C7.34 5 6 6.34 6 8s1.34 3 3 3zm7.5 3c-1.83 0-5.5.92-5.5 2.75V19h11v-2.25c0-1.83-3.67-2.75-5.5-2.75zM9 13c-2.33 0-7 1.17-7 3.5V19h7v-2.25c0-.85.33-2.34 2.37-3.47C10.5 13.1 9.66 13 9 13z"/>
                </svg>
            </div>
            <h2>Family Dashboard Login</h2>
            <p class="info-text">
                <strong>Managing multiple children?</strong> You can use any of your children's 
                account credentials to log in. Once logged in, you can link all your children's 
                memberships to manage them from one place.
            </p>
            <div class="info-tips">
                <h4>How it works:</h4>
                <ul>
                    <li>
                        <span class="tip-number">1</span>
                        Log in using your child's email/username and password
                    </li>
                    <li>
                        <span class="tip-number">2</span>
                        Link your other children's accounts to your dashboard
                    </li>
                    <li>
                        <span class="tip-number">3</span>
                        Manage all memberships, renewals, and payments in one place
                    </li>
                </ul>
            </div>
        </div>

        <!-- Login Form Section -->
        <div class="login-form-section">
            <h3>Sign In</h3>
            
            <?php if ($login_error): ?>
            <div class="login-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#dc3545">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <?php echo esc_html($login_error); ?>
            </div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(wp_login_url()); ?>" class="wcb-login-form">
                <input type="hidden" name="redirect_to" value="<?php echo esc_url($atts['redirect']); ?>">
                
                <div class="form-group">
                    <label for="user_login">Email or Username</label>
                    <input type="text" 
                           name="log" 
                           id="user_login" 
                           placeholder="Enter your child's email or username"
                           required>
                </div>

                <div class="form-group">
                    <label for="user_pass">Password</label>
                    <input type="password" 
                           name="pwd" 
                           id="user_pass" 
                           placeholder="Enter password"
                           required>
                </div>

                <div class="form-group remember-me">
                    <label>
                        <input type="checkbox" name="rememberme" value="forever">
                        Remember me
                    </label>
                </div>

                <button type="submit" name="wp-submit" class="wcb-btn wcb-btn-primary wcb-btn-full">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/>
                    </svg>
                    Sign In to Family Dashboard
                </button>
            </form>

            <div class="login-help">
                <p>
                    <a href="<?php echo esc_url(wp_lostpassword_url($atts['redirect'])); ?>">
                        Forgot your password?
                    </a>
                </p>
            </div>

            <div class="login-note">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#6c757d">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                </svg>
                <span>
                    Don't have an account? Contact West City Boxing to set up your family membership.
                </span>
            </div>
        </div>
    </div>
    <?php
    wcb_family_login_styles();
    return ob_get_clean();
}
add_shortcode('family_login', 'wcb_family_login_shortcode');

/**
 * Output styles for family login page
 */
function wcb_family_login_styles() {
    ?>
    <style>
    .wcb-family-login {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .wcb-family-login .already-logged-in {
        text-align: center;
        padding: 60px 40px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 2px solid #e9ecef;
    }

    .wcb-family-login .logged-in-icon {
        margin-bottom: 20px;
    }

    .wcb-family-login .already-logged-in h3 {
        margin: 0 0 10px 0;
        color: #27ae60;
        font-size: 24px;
    }

    .wcb-family-login .already-logged-in p {
        margin: 0 0 25px 0;
        color: #666;
        font-size: 16px;
    }

    /* Two-column layout for login page */
    @media (min-width: 768px) {
        .wcb-family-login {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: start;
        }
    }

    /* Info Section */
    .login-info-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 40px;
        border-radius: 12px;
        border: 2px solid #e9ecef;
    }

    .login-info-section .info-icon {
        margin-bottom: 20px;
    }

    .login-info-section h2 {
        margin: 0 0 15px 0;
        color: #000000;
        font-size: 28px;
        font-weight: 700;
    }

    .login-info-section .info-text {
        color: #555;
        font-size: 16px;
        line-height: 1.6;
        margin-bottom: 25px;
    }

    .login-info-section .info-tips {
        background: #ffffff;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }

    .login-info-section .info-tips h4 {
        margin: 0 0 15px 0;
        color: #000000;
        font-size: 16px;
        font-weight: 600;
    }

    .login-info-section .info-tips ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .login-info-section .info-tips li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
        color: #555;
        font-size: 14px;
        line-height: 1.5;
    }

    .login-info-section .info-tips li:last-child {
        margin-bottom: 0;
    }

    .login-info-section .tip-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: #007bff;
        color: white;
        border-radius: 50%;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* Login Form Section */
    .login-form-section {
        background: #ffffff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 2px solid #e9ecef;
    }

    .login-form-section h3 {
        margin: 0 0 25px 0;
        color: #000000;
        font-size: 24px;
        font-weight: 700;
        text-align: center;
    }

    .login-error {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8d7da;
        color: #721c24;
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .wcb-login-form .form-group {
        margin-bottom: 20px;
    }

    .wcb-login-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #000000;
        font-size: 14px;
    }

    .wcb-login-form input[type="text"],
    .wcb-login-form input[type="password"] {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 15px;
        font-family: inherit;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-sizing: border-box;
    }

    .wcb-login-form input[type="text"]:focus,
    .wcb-login-form input[type="password"]:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .wcb-login-form .remember-me {
        margin-bottom: 25px;
    }

    .wcb-login-form .remember-me label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 400;
        cursor: pointer;
    }

    .wcb-login-form .remember-me input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    /* Buttons */
    .wcb-btn {
        padding: 14px 28px;
        border: 2px solid transparent;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-size: 16px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-align: center;
        font-family: inherit;
        background: none;
        outline: none;
    }

    .wcb-btn-primary {
        background: #007bff;
        color: white !important;
        border-color: #007bff;
    }

    .wcb-btn-primary:hover {
        background: #0056b3;
        border-color: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    }

    .wcb-btn-full {
        width: 100%;
    }

    /* Login Help */
    .login-help {
        text-align: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e9ecef;
    }

    .login-help a {
        color: #007bff;
        text-decoration: none;
        font-size: 14px;
    }

    .login-help a:hover {
        text-decoration: underline;
    }

    .login-note {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        font-size: 13px;
        color: #6c757d;
        line-height: 1.5;
    }

    .login-note svg {
        flex-shrink: 0;
        margin-top: 2px;
    }

    /* Responsive */
    @media (max-width: 767px) {
        .wcb-family-login {
            padding: 15px;
        }

        .login-info-section,
        .login-form-section {
            padding: 25px 20px;
        }

        .login-info-section h2 {
            font-size: 24px;
        }

        .login-form-section h3 {
            font-size: 20px;
        }
    }
    </style>
    <?php
}

/**
 * Handle failed login redirect for family dashboard
 */
function wcb_family_login_failed_redirect($username) {
    $referrer = wp_get_referer();
    
    // Check if login came from our family login page
    if ($referrer && strpos($referrer, 'family-login') !== false) {
        wp_redirect(home_url('/family-login/?login=failed'));
        exit;
    }
}
add_action('wp_login_failed', 'wcb_family_login_failed_redirect');

/**
 * Handle empty login fields for family dashboard
 */
function wcb_family_login_empty_redirect($user, $username, $password) {
    $referrer = wp_get_referer();
    
    // Check if login came from our family login page
    if ($referrer && strpos($referrer, 'family-login') !== false) {
        if (empty($username) || empty($password)) {
            wp_redirect(home_url('/family-login/?login=failed'));
            exit;
        }
    }
    
    return $user;
}
add_filter('authenticate', 'wcb_family_login_empty_redirect', 1, 3);
