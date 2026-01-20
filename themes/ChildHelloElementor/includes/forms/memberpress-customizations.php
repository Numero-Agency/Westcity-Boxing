<?php
/**
 * MemberPress Registration Form Customizations
 * Adds helpful text for parents about login credentials
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add informational text above the email/password fields on registration forms
 */
function wcb_mepr_add_credentials_notice() {
    ?>
    <div class="wcb-credentials-notice">
        <div class="wcb-notice-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
            </svg>
        </div>
        <div class="wcb-notice-content">
            <strong>Important: Your Login Credentials</strong>
            <p>The email and password below will be used to log into the Member Portal (or Family Dashboard if you have multiple children). You'll need these credentials to activate the membership by setting up a payment method.</p>
            <p style="margin-top: 8px;"><strong>Registering multiple children?</strong> Each child requires a unique email address.</p>
        </div>
    </div>
    <style>
        .wcb-credentials-notice {
            display: flex;
            gap: 12px;
            background: #e8f4fd;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }
        .wcb-notice-icon {
            flex-shrink: 0;
            color: #0066cc;
        }
        .wcb-notice-content strong {
            display: block;
            color: #004085;
            margin-bottom: 6px;
            font-size: 14px;
        }
        .wcb-notice-content p {
            color: #004085;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }
    </style>
    <?php
}
add_action('mepr-checkout-after-custom-fields', 'wcb_mepr_add_credentials_notice');

/**
 * Modify the email field label via JavaScript since MemberPress doesn't have a filter for field labels
 */
function wcb_mepr_modify_email_label_script() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Find email label - MemberPress uses dynamic suffix so we search by partial match
        var emailLabels = document.querySelectorAll('label[for^="user_email"]');
        emailLabels.forEach(function(emailLabel) {
            if (emailLabel && emailLabel.textContent.includes('Email')) {
                emailLabel.innerHTML = 'Email:<span class="mepr-req">*</span> <small style="font-weight:normal;color:#666;display:block;margin-top:4px;">(When registering more than one child, you must use a different email for each child.)</small>';
            }
        });
    });
    </script>
    <?php
}
add_action('mepr-checkout-after-password-fields', 'wcb_mepr_modify_email_label_script');
