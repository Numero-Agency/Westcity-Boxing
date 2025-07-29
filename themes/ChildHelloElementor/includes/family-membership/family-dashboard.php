<?php
/**
 * Family Dashboard
 * Shows parents all their children's progress, sessions, and program details
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Family Dashboard Shortcode - Simplified for easy child membership renewals
 */
function wcb_family_dashboard_shortcode($atts) {
    $atts = shortcode_atts([
        'show_add_child' => 'true',
    ], $atts);

    // Check if user is logged in
    if (!is_user_logged_in()) {
        return '<div class="wcb-notice wcb-notice-error">Please log in to access your family dashboard.</div>';
    }

    $current_user_id = get_current_user_id();
    $current_user = wp_get_current_user();

    // Get all children linked to this parent (we'll create a simpler linking system)
    $children = wcb_get_parent_children($current_user_id);
    
    ob_start();
    ?>

    <div class="wcb-family-dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h2>Family Dashboard</h2>
            <div class="welcome-message">
                <p>Welcome back, <strong><?php echo esc_html($current_user->display_name); ?></strong>!</p>
                <p>Manage your West City Boxing memberships from one location.</p>
            </div>
        </div>

        <!-- Children Memberships -->
        <div class="children-memberships">
            <h3>Your Children's Memberships</h3>

            <?php if (empty($children)): ?>
            <div class="no-children-message">
                <div class="empty-state">
                    <h4>No Children Found</h4>
                    <p>We couldn't find any children linked to your account.</p>
                    <?php if ($atts['show_add_child'] === 'true'): ?>
                    <p><a href="#link-child" class="wcb-btn wcb-btn-primary">Link Your Child's Account</a></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>

            <div class="children-membership-list">
                <?php foreach ($children as $child): ?>
                <?php
                $membership_status = wcb_get_child_membership_status($child);
                $status_class = $membership_status['status']; // active, expired, expiring
                ?>
                <div class="child-membership-card">
                    <div class="child-info">
                        <h4><?php echo esc_html($child->display_name); ?></h4>
                        <p class="child-program"><?php echo esc_html($membership_status['program_name']); ?></p>
                    </div>

                    <div class="membership-status">
                        <span class="status-badge status-<?php echo esc_attr($status_class); ?>">
                            <?php echo esc_html($membership_status['status_text']); ?>
                        </span>

                        <?php if ($status_class === 'active_subscription' && isset($membership_status['subscription_info'])): ?>
                        <div class="subscription-details">
                            <p class="subscription-amount">
                                <strong>$<?php echo number_format($membership_status['subscription_info']['amount'], 2); ?></strong>
                                <?php echo esc_html($membership_status['subscription_info']['frequency']); ?>
                            </p>
                            <?php if ($membership_status['subscription_info']['next_payment']): ?>
                            <p class="next-payment">
                                Next payment: <?php echo date('M j, Y', strtotime($membership_status['subscription_info']['next_payment'])); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php elseif ($membership_status['expires_date']): ?>
                        <p class="expires-date">
                            <?php if ($status_class === 'expired'): ?>
                                Expired: <?php echo date('M j, Y', strtotime($membership_status['expires_date'])); ?>
                            <?php else: ?>
                                Expires: <?php echo date('M j, Y', strtotime($membership_status['expires_date'])); ?>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>
                    </div>

                    <!-- Main CTA Button -->
                    <div class="main-cta">
                        <?php if (isset($membership_status['payment_type']) && $membership_status['payment_type'] === 'manual'): ?>
                        <!-- Manual Payment Member - Needs Activation -->
                        <?php if (isset($membership_status['group_url'])): ?>
                        <a href="<?php echo esc_url($membership_status['group_url']); ?>"
                           class="wcb-btn activate-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 4H4C2.89 4 2 4.89 2 6V18C2 19.11 2.89 20 4 20H20C21.11 20 22 19.11 22 18V6C22 4.89 21.11 4 20 4ZM20 18H4V12H20V18ZM20 8H4V6H20V8ZM6 14H8V16H6V14Z"/>
                            </svg>
                            Choose Payment Plan
                        </a>
                        <?php else: ?>
                        <a href="/membership-plans/"
                           class="wcb-btn activate-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 4H4C2.89 4 2 4.89 2 6V18C2 19.11 2.89 20 4 20H20C21.11 20 22 19.11 22 18V6C22 4.89 21.11 4 20 4ZM20 18H4V12H20V18ZM20 8H4V6H20V8ZM6 14H8V16H6V14Z"/>
                            </svg>
                            Choose Payment Plan
                        </a>
                        <?php endif; ?>

                        <?php elseif ($status_class === 'active_subscription'): ?>
                        <!-- Active Stripe Subscription - Show Management Options -->
                        <?php if (isset($membership_status['group_url'])): ?>
                        <a href="<?php echo esc_url($membership_status['group_url']); ?>"
                           class="wcb-btn wcb-btn-outline wcb-btn-small">
                            <span class="dashicons dashicons-admin-settings"></span>
                            Change Plan
                        </a>
                        <?php endif; ?>

                        <?php elseif (isset($membership_status['payment_type']) && $membership_status['payment_type'] === 'stripe'): ?>
                        <!-- Stripe Payment Member - Show Manage Subscription -->
                        <button type="button"
                                class="wcb-btn wcb-btn-primary manage-subscription-btn"
                                data-child-id="<?php echo $child->ID; ?>"
                                data-child-name="<?php echo esc_attr($child->display_name); ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.89 1 3 1.89 3 3V21C3 22.11 3.89 23 5 23H11V21H5V3H13V9H21ZM14 10V12H22V10H14ZM14 14V16H22V14H14ZM14 18V20H22V18H14Z"/>
                            </svg>
                            Manage Subscription
                        </button>

                        <?php else: ?>
                        <!-- Fallback for unknown payment type -->
                        <a href="<?php echo esc_url($membership_status['renewal_url']); ?>"
                           class="wcb-btn wcb-btn-primary renew-btn">
                            <span class="dashicons dashicons-update"></span>
                            Renew
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- View Details Button -->
                    <button type="button"
                            class="wcb-btn wcb-btn-small wcb-btn-secondary view-details-btn"
                            data-child-id="<?php echo $child->ID; ?>"
                            data-child-name="<?php echo esc_attr($child->display_name); ?>">
                        View Details
                    </button>

                    <!-- Remove Member Button -->
                    <button type="button"
                            class="wcb-btn wcb-btn-small wcb-btn-outline remove-member-btn"
                            data-child-id="<?php echo $child->ID; ?>"
                            data-child-name="<?php echo esc_attr($child->display_name); ?>"
                            title="Remove <?php echo esc_attr($child->display_name); ?> from your family dashboard">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12S6.48 22 12 22 22 17.52 22 12 17.52 2 12 2ZM17 15.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59Z"/>
                        </svg>
                        Remove
                    </button>
                </div>
                <?php endforeach; ?>
            </div>

            <?php endif; ?>
        </div>

        <?php if ($atts['show_add_child'] === 'true'): ?>
        <!-- Link Child Section -->
        <div class="link-child-section" id="link-child">
            <h3>Link Your Child's Account</h3>
            <div class="link-child-form">
                <p>If your child already has a membership account, you can link it to your family dashboard:</p>
                <form id="link-child-form" class="simple-form">
                    <div class="form-group">
                        <label for="child_email">Child's Email or Username:</label>
                        <input type="text" id="child_email" name="child_email" placeholder="Enter child's email or username" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="wcb-btn wcb-btn-primary">
                            <span class="dashicons dashicons-admin-links"></span> Link Child Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Member Details Popup -->
        <div id="member-details-popup" class="wcb-popup-overlay" style="display: none;">
            <div class="wcb-popup-content">
                <div class="wcb-popup-header">
                    <h3 id="popup-member-name">Member Details</h3>
                    <button type="button" class="wcb-popup-close">&times;</button>
                </div>
                <div class="wcb-popup-body">
                    <div id="popup-member-details">
                        <!-- Member details will be loaded here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Management Popup -->
        <div id="subscription-management-popup" class="wcb-popup-overlay" style="display: none;">
            <div class="wcb-popup-content">
                <div class="wcb-popup-header">
                    <h3 id="subscription-popup-title">Manage Subscription</h3>
                    <button type="button" class="wcb-popup-close">&times;</button>
                </div>
                <div class="wcb-popup-body">
                    <div id="subscription-management-content">
                        <!-- Subscription management content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Family Dashboard - Using WCB Dashboard Design System */
    .wcb-family-dashboard {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        background: #ffffff;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .dashboard-header {
        background: #ffffff;
        color: #000000;
        padding: 30px 25px;
        border-radius: 12px;
        margin-bottom: 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 2px solid #e9ecef;
        text-align: center;
    }

    .dashboard-header h2 {
        margin: 0 0 15px 0;
        font-size: 32px;
        color: #007bff;
        font-weight: 700;
    }

    .welcome-message p {
        margin: 8px 0;
        color: #666666;
        font-size: 16px;
    }

    .welcome-message p:first-child {
        font-size: 18px;
        color: #000000;
        font-weight: 500;
    }
    
    .children-memberships {
        margin-bottom: 40px;
    }

    .children-memberships h3 {
        margin: 0 0 25px 0;
        color: #2c3e50;
        font-size: 24px;
        font-weight: 700;
        padding-bottom: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .children-membership-list {
        display: grid;
        gap: 20px;
    }

    .child-membership-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
        display: grid;
        grid-template-columns: 1fr auto auto auto auto;
        align-items: center;
        gap: 12px;
    }

    .child-membership-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 123, 255, 0.15);
    }
    
    .child-info h4 {
        margin: 0 0 8px 0;
        color: #000000;
        font-size: 20px;
        font-weight: 700;
    }

    .child-program {
        margin: 0;
        color: #666666;
        font-size: 14px;
        font-weight: 500;
    }

    .membership-status {
        text-align: center;
    }

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        margin-bottom: 8px;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-expiring {
        background: #fff3cd;
        color: #856404;
    }

    .status-expired {
        background: #f8d7da;
        color: #721c24;
    }

    .status-no_membership {
        background: #f8f9fa;
        color: #6c757d;
    }

    .status-needs_activation {
        background: #ffeaa7;
        color: #d63031;
    }

    .status-active_subscription {
        background: #74b9ff;
        color: #ffffff;
    }

    .expires-date {
        margin: 0;
        font-size: 12px;
        color: #666666;
    }

    .subscription-details {
        margin-top: 8px;
    }

    .subscription-amount {
        margin: 0;
        font-size: 14px;
        color: #000000;
        font-weight: 600;
    }

    .next-payment {
        margin: 5px 0 0 0;
        font-size: 12px;
        color: #666666;
    }

    .subscription-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .main-cta {
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }
    
    /* Clean Button System with Nice Colors */
    .wcb-btn {
        padding: 12px 24px;
        border: 2px solid transparent;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        text-align: center;
        font-family: inherit;
        background: none;
        outline: none;
        min-height: 48px; /* Ensure consistent height */
        box-sizing: border-box;
    }

    .wcb-btn:hover,
    .wcb-btn:focus,
    .wcb-btn:active,
    .wcb-btn:visited {
        text-decoration: none !important;
        outline: none;
    }

    .wcb-btn svg {
        flex-shrink: 0;
    }

    .wcb-btn-primary {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }

    .wcb-btn-primary:hover {
        background: #0056b3;
        border-color: #0056b3;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    }

    .wcb-btn-secondary {
        background: #6c757d;
        color: white;
        border-color: #6c757d;
    }

    .wcb-btn-secondary:hover {
        background: #545b62;
        border-color: #545b62;
        color: white !important;
        transform: translateY(-2px);
    }

    .wcb-btn-outline {
        background: transparent;
        color: #007bff;
        border-color: #007bff;
    }

    .wcb-btn-outline:hover {
        background: #007bff;
        color: white;
    }

    .wcb-btn-small {
        padding: 8px 16px;
        font-size: 12px;
    }

    /* Special CTA Buttons - Clean but Colorful */
    .activate-btn {
        background: #e74c3c;
        color: white;
        border-color: #e74c3c;
        font-weight: 600;
        text-transform: none;
        letter-spacing: normal;
    }

    .activate-btn:hover {
        background: #c0392b;
        border-color: #c0392b;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    }

    .renew-btn {
        background: #f39c12;
        color: white;
        border-color: #f39c12;
        font-weight: 600;
        text-transform: none;
        letter-spacing: normal;
    }

    .renew-btn:hover {
        background: #e67e22;
        border-color: #e67e22;
        color: white !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
    }

    /* No Children Message */
    .no-children-message {
        text-align: center;
        padding: 60px 30px;
        background: #f8f9fa;
        border-radius: 12px;
        border: 2px dashed #dee2e6;
        margin-bottom: 40px;
    }

    .empty-state h4 {
        margin: 0 0 15px 0;
        color: #000000;
        font-size: 20px;
        font-weight: 700;
    }

    .empty-state p {
        margin: 8px 0;
        color: #666666;
        font-size: 16px;
    }
    
    /* Link Child Form - Using WCB Form System */
    .link-child-section {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 2px solid #e9ecef;
        overflow: hidden;
        margin-bottom: 40px;
    }

    .link-child-section h3 {
        background: #000000;
        color: #ffffff;
        margin: 0;
        padding: 24px;
        font-size: 20px;
        font-weight: 600;
    }

    .link-child-form {
        padding: 32px;
    }

    .link-child-form p {
        margin: 0 0 24px 0;
        color: #666666;
        font-size: 16px;
    }

    .simple-form .form-group {
        margin-bottom: 24px;
    }

    .simple-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #000000;
        font-size: 14px;
    }

    .simple-form input {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        font-family: inherit;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }

    .simple-form input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
    }

    .form-actions {
        margin-top: 24px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .wcb-family-dashboard {
            padding: 15px;
        }

        .dashboard-header {
            padding: 25px 20px;
        }

        .dashboard-header h2 {
            font-size: 28px;
        }

        .child-membership-card {
            grid-template-columns: 1fr;
            text-align: center;
            gap: 15px;
        }

        .main-cta {
            justify-content: center;
        }

        .link-child-form {
            padding: 24px 20px;
        }
    }

    @media (max-width: 480px) {
        .dashboard-header h2 {
            font-size: 24px;
        }

        .child-membership-card {
            padding: 20px;
        }

        .wcb-btn {
            padding: 10px 20px;
            font-size: 13px;
        }
    }

    /* Member Details Popup - Using WCB Popup System */
    .wcb-popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
    }

    .wcb-popup-content {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        max-width: 600px;
        width: 100%;
        max-height: 80vh;
        overflow: hidden;
        position: relative;
    }

    .wcb-popup-header {
        background: #000000;
        color: #ffffff;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .wcb-popup-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
    }

    .wcb-popup-close {
        background: none;
        border: none;
        color: #ffffff;
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background-color 0.2s;
    }

    .wcb-popup-close:hover {
        background: rgba(255, 255, 255, 0.1);
    }

    .wcb-popup-body {
        padding: 24px;
        max-height: 60vh;
        overflow-y: auto;
    }

    .member-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .detail-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }

    .detail-card h4 {
        margin: 0 0 10px 0;
        color: #000000;
        font-size: 16px;
        font-weight: 600;
    }

    .detail-card p {
        margin: 5px 0;
        color: #666666;
        font-size: 14px;
    }

    .detail-card strong {
        color: #000000;
    }

    /* Subscription Management Actions */
    .subscription-management-actions,
    .manual-payment-actions {
        margin-top: 24px;
        padding-top: 24px;
        border-top: 2px solid #e9ecef;
    }

    .subscription-management-actions h4,
    .manual-payment-actions h4 {
        margin: 0 0 16px 0;
        color: #000000;
        font-size: 18px;
        font-weight: 600;
    }

    .management-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .management-buttons .wcb-btn {
        justify-self: stretch;
    }

    /* Subscription Management Popup */
    .current-subscription-info,
    .subscription-actions-section,
    .change-membership-section {
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 1px solid #e9ecef;
    }

    .change-membership-section {
        border-bottom: none;
    }

    .current-info-card {
        background: #f8f9fa;
        padding: 16px;
        border-radius: 8px;
        margin-top: 12px;
    }

    .current-info-card p {
        margin: 8px 0;
        font-size: 14px;
    }

    .action-buttons-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .group-selection-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .group-option {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        text-decoration: none;
        color: #000000;
        transition: all 0.3s ease;
        background: #ffffff;
    }

    .group-option:hover {
        border-color: #007bff;
        background: #f8f9ff;
        text-decoration: none;
        color: #000000;
    }

    .group-option.current-group {
        border-color: #28a745;
        background: #f8fff9;
    }

    .group-name {
        font-weight: 600;
        font-size: 14px;
    }

    .current-badge {
        background: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* Billing Cycles Section */
    .billing-cycles-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .billing-option {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        text-decoration: none !important;
        color: #000000;
        transition: all 0.3s ease;
        background: #ffffff;
    }

    .billing-option:hover,
    .billing-option:focus,
    .billing-option:active,
    .billing-option:visited {
        border-color: #007bff;
        background: #f8f9ff;
        text-decoration: none !important;
        color: #000000;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.1);
    }

    .billing-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .billing-name {
        font-weight: 600;
        font-size: 14px;
        color: #000000;
    }

    .billing-price {
        font-size: 13px;
        color: #007bff;
        font-weight: 600;
    }

    .billing-arrow {
        font-size: 18px;
        color: #007bff;
        font-weight: bold;
    }

    /* Recent Payments Section */
    .payments-list {
        margin-top: 16px;
    }

    .payment-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .payment-item:last-child {
        border-bottom: none;
    }

    .payment-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .payment-product {
        font-weight: 600;
        font-size: 14px;
        color: #000000;
    }

    .payment-date {
        font-size: 12px;
        color: #666666;
    }

    .payment-amount {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
    }

    .amount {
        font-weight: 700;
        font-size: 14px;
        color: #28a745;
    }

    .expires {
        font-size: 11px;
        color: #666666;
    }
    </style>

    <?php
    // Localize AJAX for family dashboard
    wp_localize_script('jquery', 'wcb_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wcb_nonce')
    ));
    ?>

    <script>
    jQuery(document).ready(function($) {
        console.log('West City Boxing Family Dashboard initialized successfully!');

        // Check if wcb_ajax is available
        if (typeof wcb_ajax !== 'undefined') {
            console.log('AJAX object available:', wcb_ajax);
        } else {
            console.error('AJAX object not available! This will cause button failures.');
        }

        // Define showNotification function if it doesn't exist (to prevent errors from dashboard.js)
        if (typeof window.showNotification === 'undefined') {
            window.showNotification = function(message, type, duration) {
                console.log('Notification (' + (type || 'info') + '):', message);
                // Simple fallback - could be enhanced with actual notifications later
            };
        }

        // Handle link child form submission
        $('#link-child-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            var $input = $form.find('#child_email');
            var childIdentifier = $input.val().trim();

            if (!childIdentifier) {
                alert('Please enter your child\'s email or username');
                return;
            }

            // Disable submit button and show loading
            $submitBtn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> Linking...');

            // AJAX request
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_link_child_to_parent',
                    child_identifier: childIdentifier,
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        if (response.data.reload) {
                            location.reload();
                        }
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('Connection error. Please try again.');
                },
                complete: function() {
                    // Re-enable submit button
                    $submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-admin-links"></span> Link Child Account');
                    $input.val('');
                }
            });
        });

        // Handle member details popup
        $('.view-details-btn').on('click', function() {
            var childId = $(this).data('child-id');
            var childName = $(this).data('child-name');

            // Update popup title
            $('#popup-member-name').text(childName + ' - Member Details');

            // Show loading in popup
            $('#popup-member-details').html('<div style="text-align: center; padding: 40px;"><span class="dashicons dashicons-update-alt" style="animation: spin 1s linear infinite;"></span> Loading member details...</div>');

            // Show popup
            $('#member-details-popup').fadeIn(300);

            // Load member details via AJAX
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_get_member_details',
                    child_id: childId,
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#popup-member-details').html(response.data.html);
                    } else {
                        $('#popup-member-details').html('<div style="text-align: center; padding: 40px; color: #dc3545;">Error loading member details: ' + response.data + '</div>');
                    }
                },
                error: function() {
                    $('#popup-member-details').html('<div style="text-align: center; padding: 40px; color: #dc3545;">Connection error. Please try again.</div>');
                }
            });
        });

        // Handle subscription management popup
        $('.manage-subscription-btn').on('click', function() {
            var childId = $(this).data('child-id');
            var childName = $(this).data('child-name');

            // Update popup title
            $('#subscription-popup-title').text(childName + ' - Manage Subscription');

            // Show loading in popup
            $('#subscription-management-content').html('<div style="text-align: center; padding: 40px;"><span class="dashicons dashicons-update-alt" style="animation: spin 1s linear infinite;"></span> Loading subscription details...</div>');

            // Show popup
            $('#subscription-management-popup').fadeIn(300);

            // Load subscription management content via AJAX
            console.log('🔄 Making AJAX call for subscription management...', {
                url: wcb_ajax.ajax_url,
                action: 'wcb_get_subscription_management',
                child_id: childId,
                nonce: wcb_ajax.nonce
            });

            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_get_subscription_management',
                    child_id: childId,
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    console.log('✅ AJAX response received:', response);
                    if (response.success) {
                        $('#subscription-management-content').html(response.data.html);
                        $('#subscription-popup-title').text(response.data.title);
                        console.log('✅ Subscription management content loaded successfully');

                        // Debug: Check what buttons were actually created
                        setTimeout(function() {
                            var cancelBtns = $('.cancel-subscription-btn');
                            var stripeBtns = $('.stripe-portal-btn');
                            console.log('🔍 Buttons found after content load:');
                            console.log('  Cancel buttons:', cancelBtns.length, cancelBtns);
                            console.log('  Stripe portal buttons:', stripeBtns.length, stripeBtns);

                            // Check if buttons have the right data attributes
                            if (cancelBtns.length > 0) {
                                console.log('  Cancel button data-child-id:', cancelBtns.first().data('child-id'));
                                console.log('  Cancel button data-child-name:', cancelBtns.first().data('child-name'));
                            }
                            if (stripeBtns.length > 0) {
                                console.log('  Stripe button data-child-id:', stripeBtns.first().data('child-id'));
                            }

                            // Bind the actual working event handlers
                            console.log('🔧 Binding working event handlers...');

                            // Cancel Subscription Handler
                            cancelBtns.off('click').on('click', function() {
                                var childId = $(this).data('child-id');
                                var childName = $(this).data('child-name');
                                console.log('🔴 Cancel subscription clicked!', childId, childName);

                                if (confirm('Are you sure you want to cancel ' + childName + '\'s subscription? This action cannot be undone.')) {
                                    var $btn = $(this);
                                    var originalHtml = $btn.html();
                                    $btn.prop('disabled', true).html('<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="animation: spin 1s linear infinite;"><path d="M12 4V2A10 10 0 0 0 2 12H4A8 8 0 0 1 12 4Z"/></svg> Cancelling...');

                                    $.ajax({
                                        url: wcb_ajax.ajax_url,
                                        type: 'POST',
                                        data: {
                                            action: 'wcb_cancel_subscription',
                                            child_id: childId,
                                            nonce: wcb_ajax.nonce
                                        },
                                        success: function(response) {
                                            console.log('Cancel subscription response:', response);
                                            if (response.success) {
                                                alert(response.data.message);
                                                $('#subscription-management-popup').fadeOut(300);
                                                location.reload();
                                            } else {
                                                alert('Error: ' + response.data);
                                                $btn.prop('disabled', false).html(originalHtml);
                                            }
                                        },
                                        error: function() {
                                            alert('Connection error. Please try again.');
                                            $btn.prop('disabled', false).html(originalHtml);
                                        }
                                    });
                                }
                            });

                            // Stripe Portal Handler
                            stripeBtns.off('click').on('click', function() {
                                var childId = $(this).data('child-id');
                                var $btn = $(this);
                                var originalHtml = $btn.html();
                                console.log('🔵 Stripe portal clicked!', childId);

                                $btn.prop('disabled', true).html('<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="animation: spin 1s linear infinite;"><path d="M12 4V2A10 10 0 0 0 2 12H4A8 8 0 0 1 12 4Z"/></svg> Loading...');

                                $.ajax({
                                    url: wcb_ajax.ajax_url,
                                    type: 'POST',
                                    data: {
                                        action: 'wcb_get_stripe_portal',
                                        child_id: childId,
                                        nonce: wcb_ajax.nonce
                                    },
                                    success: function(response) {
                                        console.log('Stripe portal response:', response);
                                        if (response.success) {
                                            window.open(response.data.portal_url, '_blank');
                                            $btn.prop('disabled', false).html(originalHtml);
                                        } else {
                                            alert('Error: ' + response.data);
                                            $btn.prop('disabled', false).html(originalHtml);
                                        }
                                    },
                                    error: function() {
                                        alert('Connection error. Please try again.');
                                        $btn.prop('disabled', false).html(originalHtml);
                                    }
                                });
                            });
                        }, 100);
                    } else {
                        console.error('❌ AJAX success but response failed:', response.data);
                        $('#subscription-management-content').html('<div style="text-align: center; padding: 40px; color: #dc3545;">Error loading subscription details: ' + response.data + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ AJAX error:', {xhr: xhr, status: status, error: error});
                    $('#subscription-management-content').html('<div style="text-align: center; padding: 40px; color: #dc3545;">Connection error. Please try again.</div>');
                }
            });
        });

        // Close popup
        $('.wcb-popup-close, .wcb-popup-overlay').on('click', function(e) {
            if (e.target === this) {
                $('#member-details-popup').fadeOut(300);
                $('#subscription-management-popup').fadeOut(300);
            }
        });

        // Prevent popup content clicks from closing popup
        $('.wcb-popup-content').on('click', function(e) {
            e.stopPropagation();
        });

        // Handle subscription management actions (using event delegation for dynamically loaded content)
        $(document).on('click', '.cancel-subscription-btn', function() {
            var childId = $(this).data('child-id');
            var childName = $(this).data('child-name');

            if (confirm('Are you sure you want to cancel ' + childName + '\'s subscription? This action cannot be undone.')) {
                var $btn = $(this);
                var originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="animation: spin 1s linear infinite;"><path d="M12 4V2A10 10 0 0 0 2 12H4A8 8 0 0 1 12 4Z"/></svg> Cancelling...');

                $.ajax({
                    url: wcb_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wcb_cancel_subscription',
                        child_id: childId,
                        nonce: wcb_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            $('#subscription-management-popup').fadeOut(300);
                            location.reload(); // Refresh to show updated status
                        } else {
                            alert('Error: ' + response.data);
                            $btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function() {
                        alert('Connection error. Please try again.');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            }
        });

        // Handle Stripe Customer Portal
        $(document).on('click', '.stripe-portal-btn', function() {
            var childId = $(this).data('child-id');
            var $btn = $(this);
            var originalHtml = $btn.html();

            $btn.prop('disabled', true).html('<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="animation: spin 1s linear infinite;"><path d="M12 4V2A10 10 0 0 0 2 12H4A8 8 0 0 1 12 4Z"/></svg> Loading...');

            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_get_stripe_portal',
                    child_id: childId,
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Open Stripe portal in new tab
                        window.open(response.data.portal_url, '_blank');
                        $btn.prop('disabled', false).html(originalHtml);
                    } else {
                        alert('Error: ' + response.data);
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function() {
                    alert('Connection error. Please try again.');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        // Handle Update Payment Method
        $(document).on('click', '.update-payment-btn', function() {
            alert('Update Payment Method: This would redirect to a secure payment update form or Stripe portal.');
        });

        // Handle Remove Member
        $(document).on('click', '.remove-member-btn', function() {
            var childId = $(this).data('child-id');
            var childName = $(this).data('child-name');

            if (confirm('Are you sure you want to remove ' + childName + ' from your family dashboard?\n\nThis will only remove them from your dashboard - their membership will remain active.')) {
                var $btn = $(this);
                var originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="animation: spin 1s linear infinite;"><path d="M12 4V2A10 10 0 0 0 2 12H4A8 8 0 0 1 12 4Z"/></svg> Removing...');

                $.ajax({
                    url: wcb_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'wcb_remove_linked_child',
                        child_id: childId,
                        nonce: wcb_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Remove the card from the UI
                            $btn.closest('.child-membership-card').fadeOut(300, function() {
                                $(this).remove();
                                // Check if no children left
                                if ($('.child-membership-card').length === 0) {
                                    location.reload();
                                }
                            });
                        } else {
                            alert('Error: ' + response.data);
                            $btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function() {
                        alert('Connection error. Please try again.');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            }
        });

        // Add spin animation for loading
        $('<style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>').appendTo('head');

        // Add manual test function to window for debugging
        window.testSubscriptionActions = function() {
            console.log('🧪 Testing subscription actions...');
            console.log('Cancel buttons found:', $('.cancel-subscription-btn').length);
            console.log('Stripe portal buttons found:', $('.stripe-portal-btn').length);
            console.log('Manage subscription buttons found:', $('.manage-subscription-btn').length);

            // Try to manually trigger cancel subscription
            if ($('.cancel-subscription-btn').length > 0) {
                console.log('🧪 Manually triggering cancel subscription...');
                $('.cancel-subscription-btn').first().trigger('click');
            }
        };

        // Debug: Check if buttons exist and log all clicks
        $(document).on('click', '*', function(e) {
            if ($(this).hasClass('cancel-subscription-btn')) {
                console.log('🔴 Cancel subscription button clicked!', $(this));
                e.stopPropagation();
            }
            if ($(this).hasClass('stripe-portal-btn')) {
                console.log('🔵 Stripe portal button clicked!', $(this));
                e.stopPropagation();
            }
            if ($(this).hasClass('manage-subscription-btn')) {
                console.log('🟡 Manage subscription button clicked!', $(this));
            }
        });

        // Also try direct event binding after popup loads
        $(document).on('DOMNodeInserted', function(e) {
            if ($(e.target).find('.cancel-subscription-btn').length > 0) {
                console.log('🟢 Cancel subscription buttons found in DOM:', $(e.target).find('.cancel-subscription-btn'));
            }
            if ($(e.target).find('.stripe-portal-btn').length > 0) {
                console.log('🟢 Stripe portal buttons found in DOM:', $(e.target).find('.stripe-portal-btn'));
            }
        });
    });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('family_dashboard', 'wcb_family_dashboard_shortcode');
