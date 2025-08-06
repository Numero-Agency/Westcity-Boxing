<?php
/**
 * Family Registration AJAX Handlers
 * Handles form submissions for family membership registration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add Child AJAX Handler
 */
function wcb_ajax_add_family_child() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in');
        return;
    }
    
    $current_user_id = get_current_user_id();
    
    // Get family membership
    $family_membership = wcb_get_family_membership_by_parent($current_user_id);
    
    if (!$family_membership) {
        wp_send_json_error('No family membership found');
        return;
    }
    
    // Check if family has reached maximum children
    $existing_children = wcb_get_family_children($family_membership->id);
    if (count($existing_children) >= $family_membership->max_children) {
        wp_send_json_error('Maximum number of children reached for your family plan');
        return;
    }
    
    // Sanitize input data
    $child_name = sanitize_text_field($_POST['child_name']);
    $child_age = intval($_POST['child_age']);
    $child_email = sanitize_email($_POST['child_email']);
    $program_group = sanitize_text_field($_POST['program_group']);
    
    // Validate required fields
    if (empty($child_name) || empty($program_group)) {
        wp_send_json_error('Child name and program group are required');
        return;
    }
    
    // Validate program group
    $valid_groups = array_keys(wcb_get_program_groups());
    if (!in_array($program_group, $valid_groups)) {
        wp_send_json_error('Invalid program group selected');
        return;
    }
    
    // Insert child into database
    global $wpdb;
    $table = $wpdb->prefix . 'wcb_family_children';
    
    $result = $wpdb->insert(
        $table,
        [
            'family_id' => $family_membership->id,
            'child_name' => $child_name,
            'child_age' => $child_age ?: null,
            'child_email' => $child_email ?: null,
            'program_group' => $program_group,
            'status' => 'active'
        ],
        [
            '%d',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s'
        ]
    );
    
    if ($result === false) {
        wp_send_json_error('Failed to add child to database');
        return;
    }
    
    $child_id = $wpdb->insert_id;
    
    // Return success with child data
    wp_send_json_success([
        'message' => 'Child added successfully',
        'child' => [
            'id' => $child_id,
            'name' => $child_name,
            'age' => $child_age,
            'email' => $child_email,
            'program_group' => $program_group,
            'program_name' => wcb_get_program_group_name($program_group),
            'status' => 'active'
        ]
    ]);
}
add_action('wp_ajax_wcb_add_family_child', 'wcb_ajax_add_family_child');
add_action('wp_ajax_nopriv_wcb_add_family_child', 'wcb_ajax_add_family_child');

/**
 * Edit Child AJAX Handler
 */
function wcb_ajax_edit_family_child() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in');
        return;
    }
    
    $current_user_id = get_current_user_id();
    $child_id = intval($_POST['child_id']);
    
    // Get family membership
    $family_membership = wcb_get_family_membership_by_parent($current_user_id);
    
    if (!$family_membership) {
        wp_send_json_error('No family membership found');
        return;
    }
    
    // Verify child belongs to this family
    global $wpdb;
    $table = $wpdb->prefix . 'wcb_family_children';
    
    $child = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d AND family_id = %d",
        $child_id,
        $family_membership->id
    ));
    
    if (!$child) {
        wp_send_json_error('Child not found or does not belong to your family');
        return;
    }
    
    // Sanitize input data
    $child_name = sanitize_text_field($_POST['child_name']);
    $child_age = intval($_POST['child_age']);
    $child_email = sanitize_email($_POST['child_email']);
    $program_group = sanitize_text_field($_POST['program_group']);
    $child_status = sanitize_text_field($_POST['child_status']);
    
    // Validate required fields
    if (empty($child_name) || empty($program_group)) {
        wp_send_json_error('Child name and program group are required');
        return;
    }
    
    // Validate program group
    $valid_groups = array_keys(wcb_get_program_groups());
    if (!in_array($program_group, $valid_groups)) {
        wp_send_json_error('Invalid program group selected');
        return;
    }
    
    // Validate status
    if (!in_array($child_status, ['active', 'inactive'])) {
        wp_send_json_error('Invalid status selected');
        return;
    }
    
    // Update child in database
    $result = $wpdb->update(
        $table,
        [
            'child_name' => $child_name,
            'child_age' => $child_age ?: null,
            'child_email' => $child_email ?: null,
            'program_group' => $program_group,
            'status' => $child_status
        ],
        ['id' => $child_id],
        [
            '%s',
            '%d',
            '%s',
            '%s',
            '%s'
        ],
        ['%d']
    );
    
    if ($result === false) {
        wp_send_json_error('Failed to update child in database');
        return;
    }
    
    // Return success with updated child data
    wp_send_json_success([
        'message' => 'Child updated successfully',
        'child' => [
            'id' => $child_id,
            'name' => $child_name,
            'age' => $child_age,
            'email' => $child_email,
            'program_group' => $program_group,
            'program_name' => wcb_get_program_group_name($program_group),
            'status' => $child_status
        ]
    ]);
}
add_action('wp_ajax_wcb_edit_family_child', 'wcb_ajax_edit_family_child');
add_action('wp_ajax_nopriv_wcb_edit_family_child', 'wcb_ajax_edit_family_child');

/**
 * Remove Child AJAX Handler
 */
function wcb_ajax_remove_family_child() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in');
        return;
    }
    
    $current_user_id = get_current_user_id();
    $child_id = intval($_POST['child_id']);
    
    // Get family membership
    $family_membership = wcb_get_family_membership_by_parent($current_user_id);
    
    if (!$family_membership) {
        wp_send_json_error('No family membership found');
        return;
    }
    
    // Verify child belongs to this family
    global $wpdb;
    $table = $wpdb->prefix . 'wcb_family_children';
    
    $child = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d AND family_id = %d",
        $child_id,
        $family_membership->id
    ));
    
    if (!$child) {
        wp_send_json_error('Child not found or does not belong to your family');
        return;
    }
    
    // Soft delete - set status to inactive instead of deleting
    $result = $wpdb->update(
        $table,
        ['status' => 'inactive'],
        ['id' => $child_id],
        ['%s'],
        ['%d']
    );
    
    if ($result === false) {
        wp_send_json_error('Failed to remove child');
        return;
    }
    
    wp_send_json_success([
        'message' => 'Child removed successfully',
        'child_id' => $child_id
    ]);
}
add_action('wp_ajax_wcb_remove_family_child', 'wcb_ajax_remove_family_child');
add_action('wp_ajax_nopriv_wcb_remove_family_child', 'wcb_ajax_remove_family_child');

/**
 * Get Child Details AJAX Handler
 */
function wcb_ajax_get_family_child_details() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in');
        return;
    }
    
    $current_user_id = get_current_user_id();
    $child_id = intval($_POST['child_id']);
    
    // Get family membership
    $family_membership = wcb_get_family_membership_by_parent($current_user_id);
    
    if (!$family_membership) {
        wp_send_json_error('No family membership found');
        return;
    }
    
    // Get child details
    global $wpdb;
    $table = $wpdb->prefix . 'wcb_family_children';
    
    $child = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d AND family_id = %d",
        $child_id,
        $family_membership->id
    ));
    
    if (!$child) {
        wp_send_json_error('Child not found or does not belong to your family');
        return;
    }
    
    wp_send_json_success([
        'child' => [
            'id' => $child->id,
            'name' => $child->child_name,
            'age' => $child->child_age,
            'email' => $child->child_email,
            'program_group' => $child->program_group,
            'status' => $child->status
        ]
    ]);
}
add_action('wp_ajax_wcb_get_family_child_details', 'wcb_ajax_get_family_child_details');
add_action('wp_ajax_nopriv_wcb_get_family_child_details', 'wcb_ajax_get_family_child_details');

/**
 * Link Child to Parent AJAX Handler
 */
function wcb_ajax_link_child_to_parent() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in');
        return;
    }

    $parent_user_id = get_current_user_id();
    $child_identifier = sanitize_text_field($_POST['child_identifier']);

    if (empty($child_identifier)) {
        wp_send_json_error('Please enter your child\'s email or username');
        return;
    }

    // Try to link the child
    $result = wcb_link_child_to_parent($parent_user_id, $child_identifier);

    if ($result['success']) {
        wp_send_json_success([
            'message' => $result['message'],
            'reload' => true
        ]);
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_wcb_link_child_to_parent', 'wcb_ajax_link_child_to_parent');
add_action('wp_ajax_nopriv_wcb_link_child_to_parent', 'wcb_ajax_link_child_to_parent');

/**
 * Get Member Details AJAX Handler
 */
function wcb_ajax_get_member_details() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in');
        return;
    }

    $child_id = intval($_POST['child_id']);
    $parent_user_id = get_current_user_id();

    // Verify this child is linked to the parent
    $linked_children = get_user_meta($parent_user_id, 'wcb_linked_children', true);
    if (!is_array($linked_children) || !in_array($child_id, $linked_children)) {
        wp_send_json_error('You do not have permission to view this member\'s details');
        return;
    }

    // Get child user
    $child_user = get_user_by('ID', $child_id);
    if (!$child_user) {
        wp_send_json_error('Member not found');
        return;
    }

    // Get membership status
    $membership_status = wcb_get_child_membership_status($child_user);

    // Get additional member details
    global $wpdb;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Get transaction history
    $transactions = $wpdb->get_results($wpdb->prepare("
        SELECT t.*, p.post_title as product_name
        FROM {$txn_table} t
        JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        ORDER BY t.created_at DESC
        LIMIT 5
    ", $child_id));

    // Build HTML response
    ob_start();
    ?>
    <div class="member-detail-grid">
        <div class="detail-card">
            <h4>👤 Member Information</h4>
            <p><strong>Name:</strong> <?php echo esc_html($child_user->display_name); ?></p>
            <p><strong>Email:</strong> <?php echo esc_html($child_user->user_email ?: 'Not provided'); ?></p>
            <p><strong>Username:</strong> <?php echo esc_html($child_user->user_login); ?></p>
            <p><strong>Member Since:</strong> <?php echo date('M j, Y', strtotime($child_user->user_registered)); ?></p>
        </div>

        <div class="detail-card">
            <h4>🥊 Current Membership</h4>
            <p><strong>Program:</strong> <?php echo esc_html($membership_status['program_name']); ?></p>
            <p><strong>Status:</strong>
                <span class="status-badge status-<?php echo esc_attr($membership_status['status']); ?>">
                    <?php echo esc_html($membership_status['status_text']); ?>
                </span>
            </p>
            <?php if ($membership_status['expires_date']): ?>
            <p><strong>Expires:</strong> <?php echo date('M j, Y', strtotime($membership_status['expires_date'])); ?></p>
            <?php endif; ?>
            <?php if (isset($membership_status['subscription_info'])): ?>
            <p><strong>Billing:</strong> $<?php echo number_format($membership_status['subscription_info']['amount'], 2); ?> <?php echo esc_html($membership_status['subscription_info']['frequency']); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php

    $html = ob_get_clean();

    wp_send_json_success([
        'html' => $html
    ]);
}
add_action('wp_ajax_wcb_get_member_details', 'wcb_ajax_get_member_details');
add_action('wp_ajax_nopriv_wcb_get_member_details', 'wcb_ajax_get_member_details');

/**
 * Send Parent Invitation AJAX Handler
 */
function wcb_ajax_send_parent_invitation() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    // Check permissions
    if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
        wp_send_json_error('You do not have permission to send invitations');
        return;
    }

    $parent_name = sanitize_text_field($_POST['parent_name']);
    $parent_email = sanitize_email($_POST['parent_email']);
    $children_names = sanitize_textarea_field($_POST['children_names']);
    $personal_message = sanitize_textarea_field($_POST['personal_message']);

    if (empty($parent_name) || empty($parent_email)) {
        wp_send_json_error('Parent name and email are required');
        return;
    }

    if (!is_email($parent_email)) {
        wp_send_json_error('Please enter a valid email address');
        return;
    }

    // Check if email already has a pending invitation
    global $wpdb;
    $table_name = $wpdb->prefix . 'wcb_parent_invitations';
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE parent_email = %s AND status = 'pending'",
        $parent_email
    ));

    if ($existing > 0) {
        wp_send_json_error('This email already has a pending invitation');
        return;
    }

    // Create invitation
    $token = wcb_create_parent_invitation($parent_name, $parent_email, $children_names, $personal_message);

    if (!$token) {
        wp_send_json_error('Failed to create invitation');
        return;
    }

    // Send email
    $email_sent = wcb_send_parent_invitation_email($parent_name, $parent_email, $token, $children_names, $personal_message);

    if ($email_sent) {
        wp_send_json_success([
            'message' => 'Invitation sent successfully to ' . $parent_email
        ]);
    } else {
        wp_send_json_error('Invitation created but email failed to send. Please contact the parent directly.');
    }
}
add_action('wp_ajax_wcb_send_parent_invitation', 'wcb_ajax_send_parent_invitation');

/**
 * Complete Parent Registration AJAX Handler
 */
function wcb_ajax_complete_parent_registration() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    $token = sanitize_text_field($_POST['token']);
    $username = sanitize_user($_POST['username']);
    $password = $_POST['password']; // Don't sanitize passwords
    $phone = sanitize_text_field($_POST['phone']);

    if (empty($token) || empty($username) || empty($password)) {
        wp_send_json_error('All required fields must be filled');
        return;
    }

    if (strlen($password) < 8) {
        wp_send_json_error('Password must be at least 8 characters long');
        return;
    }

    // Complete registration
    $result = wcb_complete_parent_registration($token, $username, $password, $phone);

    if ($result['success']) {
        wp_send_json_success([
            'message' => $result['message'],
            'redirect_url' => home_url('/family-dashboard/')
        ]);
    } else {
        wp_send_json_error($result['message']);
    }
}
add_action('wp_ajax_wcb_complete_parent_registration', 'wcb_ajax_complete_parent_registration');
add_action('wp_ajax_nopriv_wcb_complete_parent_registration', 'wcb_ajax_complete_parent_registration');

/**
 * Simple Parent Registration AJAX Handler (no token required)
 */
function wcb_ajax_simple_parent_registration() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    $parent_name = sanitize_text_field($_POST['parent_name']);
    $parent_email = sanitize_email($_POST['parent_email']);
    $username = sanitize_user($_POST['username']);
    $password = $_POST['password']; // Don't sanitize passwords
    $phone = sanitize_text_field($_POST['phone']);

    if (empty($parent_name) || empty($parent_email) || empty($username) || empty($password)) {
        wp_send_json_error('All required fields must be filled');
        return;
    }

    if (!is_email($parent_email)) {
        wp_send_json_error('Please enter a valid email address');
        return;
    }

    if (strlen($password) < 8) {
        wp_send_json_error('Password must be at least 8 characters long');
        return;
    }

    // Check if username already exists
    if (username_exists($username)) {
        wp_send_json_error('Username already exists. Please choose a different username.');
        return;
    }

    // Check if email already exists
    if (email_exists($parent_email)) {
        wp_send_json_error('An account with this email already exists.');
        return;
    }

    // Create user account
    $user_id = wp_create_user($username, $password, $parent_email);

    if (is_wp_error($user_id)) {
        wp_send_json_error($user_id->get_error_message());
        return;
    }

    // Update user profile
    wp_update_user([
        'ID' => $user_id,
        'display_name' => $parent_name,
        'first_name' => explode(' ', $parent_name)[0],
        'last_name' => substr($parent_name, strpos($parent_name, ' ') + 1)
    ]);

    // Add phone number if provided
    if ($phone) {
        update_user_meta($user_id, 'phone', $phone);
    }

    // Log the user in
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    wp_send_json_success([
        'message' => 'Registration completed successfully! Redirecting to your family dashboard...',
        'redirect_url' => home_url('/family-dashboard/')
    ]);
}
add_action('wp_ajax_wcb_simple_parent_registration', 'wcb_ajax_simple_parent_registration');
add_action('wp_ajax_nopriv_wcb_simple_parent_registration', 'wcb_ajax_simple_parent_registration');

/**
 * Cancel Subscription AJAX Handler
 */
function wcb_ajax_cancel_subscription() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in');
        return;
    }

    $child_id = intval($_POST['child_id']);
    $parent_user_id = get_current_user_id();

    // Verify this child is linked to the parent
    $linked_children = get_user_meta($parent_user_id, 'wcb_linked_children', true);
    if (!is_array($linked_children) || !in_array($child_id, $linked_children)) {
        wp_send_json_error('You do not have permission to manage this subscription');
        return;
    }

    // Get child's subscription
    global $wpdb;
    $subscription_table = $wpdb->prefix . 'mepr_subscriptions';

    $subscription = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $subscription_table
         WHERE user_id = %d
         AND status = 'active'
         ORDER BY created_at DESC
         LIMIT 1",
        $child_id
    ));

    if (!$subscription) {
        wp_send_json_error('No active subscription found');
        return;
    }

    // Cancel the subscription (set status to cancelled)
    $result = $wpdb->update(
        $subscription_table,
        ['status' => 'cancelled'],
        ['id' => $subscription->id],
        ['%s'],
        ['%d']
    );

    if ($result !== false) {
        wp_send_json_success([
            'message' => 'Subscription cancelled successfully. The membership will remain active until the current billing period ends.'
        ]);
    } else {
        wp_send_json_error('Failed to cancel subscription. Please try again.');
    }
}
add_action('wp_ajax_wcb_cancel_subscription', 'wcb_ajax_cancel_subscription');

/**
 * Get Stripe Customer Portal URL AJAX Handler
 */
function wcb_ajax_get_stripe_portal() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in');
        return;
    }

    $child_id = intval($_POST['child_id']);
    $parent_user_id = get_current_user_id();

    // Verify this child is linked to the parent
    $linked_children = get_user_meta($parent_user_id, 'wcb_linked_children', true);
    if (!is_array($linked_children) || !in_array($child_id, $linked_children)) {
        wp_send_json_error('You do not have permission to access this portal');
        return;
    }

    // Get child user
    $child_user = get_user_by('ID', $child_id);
    if (!$child_user) {
        wp_send_json_error('Member not found');
        return;
    }

    // Try to get Stripe customer ID from MemberPress
    $stripe_customer_id = get_user_meta($child_id, 'mepr_stripe_customer_id', true);

    if ($stripe_customer_id) {
        // Generate Stripe customer portal URL
        $portal_url = 'https://billing.stripe.com/p/login/' . $stripe_customer_id;
    } else {
        // Fallback to general Stripe billing portal
        $portal_url = 'https://billing.stripe.com/';
    }

    wp_send_json_success([
        'portal_url' => $portal_url,
        'message' => 'Opening Stripe Customer Portal...'
    ]);
}
add_action('wp_ajax_wcb_get_stripe_portal', 'wcb_ajax_get_stripe_portal');

/**
 * Get Subscription Management Content AJAX Handler
 */
function wcb_ajax_get_subscription_management() {
    try {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
            wp_send_json_error('Security check failed');
            return;
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in');
            return;
        }

        $child_id = intval($_POST['child_id']);
        $parent_user_id = get_current_user_id();

        // Verify this child is linked to the parent
        $linked_children = get_user_meta($parent_user_id, 'wcb_linked_children', true);
        if (!is_array($linked_children) || !in_array($child_id, $linked_children)) {
            wp_send_json_error('You do not have permission to manage this subscription');
            return;
        }

        // Get child user
        $child_user = get_user_by('ID', $child_id);
        if (!$child_user) {
            wp_send_json_error('Member not found');
            return;
        }

        // Get membership status
        $membership_status = wcb_get_child_membership_status($child_user);

    // Get billing cycles within the current group using the EXACT same logic as active-members-test.php
    $current_group_info = $membership_status['group_name'] ?? '';
    $group_url = $membership_status['group_url'] ?? '';

    // Map group names to their MemberPress group IDs (same as active-members-test.php)
    $group_id_mapping = [
        'Mini Cadet Boys (9-11 Years) Group 1' => 1767,
        'Cadet Boys Group 1' => 1786,
        'Cadet Boys Group 2' => 1790,
        'Youth Boys Group 1' => 1803,
        'Youth Boys Group 2' => 1809,
        'Mini Cadets Girls Group 1' => 1812,
        'Youth Girls Group 1' => 1815
    ];

    // Get billing cycles for the current group
    $billing_cycles = [];
    $current_group_id = null;

    // Find the group ID for the current member's group
    foreach ($group_id_mapping as $group_name => $group_id) {
        if (strpos($current_group_info, $group_name) !== false || $current_group_info === $group_name) {
            $current_group_id = $group_id;
            break;
        }
    }

    if ($current_group_id) {
        // Get group memberships excluding monthly (for display only)
        $group_memberships = wcb_get_group_memberships_for_display($current_group_id);

        foreach ($group_memberships as $membership) {
            $price = get_post_meta($membership->ID, '_mepr_product_price', true);
            $billing_cycles[] = [
                'id' => $membership->ID,
                'name' => $membership->post_title,
                'price' => $price ? '$' . number_format($price, 2) : 'Price TBA',
                'url' => get_permalink($membership->ID)
            ];
        }
    }

    // Get transaction history for recent payments
    global $wpdb;
    $transactions = $wpdb->get_results($wpdb->prepare("
        SELECT t.*, p.post_title as product_name
        FROM {$wpdb->prefix}mepr_transactions t
        JOIN {$wpdb->posts} p ON t.product_id = p.ID
        WHERE t.user_id = %d
        AND t.status IN ('confirmed', 'complete')
        ORDER BY t.created_at DESC
        LIMIT 5
    ", $child_id));

    // Build HTML response
    ob_start();
    ?>
    <div class="subscription-management-content">
        <!-- 1. Current Subscription Details -->
        <div class="current-subscription-info">
            <h4>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 8px;">
                    <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM19 19H5V5H19V19ZM17 12H7V10H17V12ZM17 16H7V14H17V16ZM17 8H7V6H17V8Z"/>
                </svg>
                Current Subscription Details
            </h4>
            <div class="current-info-card">
                <p><strong>Member:</strong> <?php echo esc_html($child_user->display_name); ?></p>
                <p><strong>Program:</strong> <?php echo esc_html($membership_status['program_name']); ?></p>
                <p><strong>Status:</strong>
                    <span class="status-badge status-<?php echo esc_attr($membership_status['status']); ?>">
                        <?php echo esc_html($membership_status['status_text']); ?>
                    </span>
                </p>
                <?php if (isset($membership_status['subscription_info'])): ?>
                <p><strong>Billing:</strong> $<?php echo number_format($membership_status['subscription_info']['amount'], 2); ?> <?php echo esc_html($membership_status['subscription_info']['frequency']); ?></p>
                <?php if ($membership_status['subscription_info']['next_payment']): ?>
                <p><strong>Next Payment:</strong> <?php echo date('M j, Y', strtotime($membership_status['subscription_info']['next_payment'])); ?></p>
                <?php endif; ?>
                <?php endif; ?>

                <?php
                // Get subscription start and due dates
                global $wpdb;
                $subscription_table = $wpdb->prefix . 'mepr_subscriptions';
                $subscription_dates = $wpdb->get_row($wpdb->prepare(
                    "SELECT created_at, expires_at FROM $subscription_table
                     WHERE user_id = %d
                     AND status = 'active'
                     ORDER BY created_at DESC
                     LIMIT 1",
                    $child_id
                ));

                if ($subscription_dates): ?>
                <p><strong>Started:</strong> <?php echo date('M j, Y', strtotime($subscription_dates->created_at)); ?></p>
                <?php if ($subscription_dates->expires_at && $subscription_dates->expires_at !== '0000-00-00 00:00:00'): ?>
                <p><strong>Due Date:</strong> <?php echo date('M j, Y', strtotime($subscription_dates->expires_at)); ?></p>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- 2. Recent Payments -->
        <?php if (!empty($transactions)): ?>
        <div class="recent-payments-section">
            <h4>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 8px;">
                    <path d="M20 4H4C2.89 4 2 4.89 2 6V18C2 19.11 2.89 20 4 20H20C21.11 20 22 19.11 22 18V6C22 4.89 21.11 4 20 4ZM20 18H4V12H20V18ZM20 8H4V6H20V8Z"/>
                </svg>
                Recent Payments
            </h4>
            <div class="payments-list">
                <?php foreach ($transactions as $transaction): ?>
                <div class="payment-item">
                    <div class="payment-info">
                        <span class="payment-product"><?php echo esc_html($transaction->product_name); ?></span>
                        <span class="payment-date"><?php echo date('M j, Y', strtotime($transaction->created_at)); ?></span>
                    </div>
                    <div class="payment-amount">
                        <span class="amount">$<?php echo number_format($transaction->total, 2); ?></span>
                        <?php if ($transaction->expires_at && $transaction->expires_at !== '0000-00-00 00:00:00'): ?>
                        <span class="expires">Expires: <?php echo date('M j, Y', strtotime($transaction->expires_at)); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- 3. Change Billing Cycle -->
        <div class="change-billing-section">
            <h4>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 8px;">
                    <path d="M12 4V1L8 5L12 9V6C15.31 6 18 8.69 18 12C18 13.01 17.75 13.97 17.3 14.8L18.76 16.26C19.54 15.03 20 13.57 20 12C20 7.58 16.42 4 12 4ZM12 18C8.69 18 6 15.31 6 12C6 10.99 6.25 10.03 6.7 9.2L5.24 7.74C4.46 8.97 4 10.43 4 12C4 16.42 7.58 20 12 20V23L16 19L12 15V18Z"/>
                </svg>
                Change Billing Cycle
            </h4>
            <p style="color: #666; font-size: 14px; margin-bottom: 16px;">
                Switch to a different billing cycle within <strong><?php echo esc_html($current_group_info); ?></strong>
            </p>

            <?php if (!empty($billing_cycles)): ?>
            <div class="billing-cycles-grid">
                <?php foreach ($billing_cycles as $cycle): ?>
                <a href="<?php echo esc_url($cycle['url']); ?>"
                   class="billing-option"
                   target="_blank">
                    <div class="billing-info">
                        <span class="billing-name"><?php echo esc_html($cycle['name']); ?></span>
                        <span class="billing-price"><?php echo esc_html($cycle['price']); ?></span>
                    </div>
                    <span class="billing-arrow">→</span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="color: #666; font-style: italic;">No billing cycles available for this program.</p>
            <?php endif; ?>
        </div>

        <!-- 4. Subscription Actions -->
        <div class="subscription-actions-section">
            <h4>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 8px;">
                    <path d="M12 15.5A3.5 3.5 0 0 1 8.5 12A3.5 3.5 0 0 1 12 8.5A3.5 3.5 0 0 1 15.5 12A3.5 3.5 0 0 1 12 15.5M19.43 12.98C19.47 12.66 19.5 12.34 19.5 12C19.5 11.66 19.47 11.34 19.43 11.02L21.54 9.37C21.73 9.22 21.78 8.95 21.66 8.73L19.66 5.27C19.54 5.05 19.27 4.96 19.05 5.05L16.56 6.05C16.04 5.66 15.5 5.32 14.87 5.07L14.5 2.42C14.46 2.18 14.25 2 14 2H10C9.75 2 9.54 2.18 9.5 2.42L9.13 5.07C8.5 5.32 7.96 5.66 7.44 6.05L4.95 5.05C4.73 4.96 4.46 5.05 4.34 5.27L2.34 8.73C2.22 8.95 2.27 9.22 2.46 9.37L4.57 11.02C4.53 11.34 4.5 11.67 4.5 12C4.5 12.33 4.53 12.66 4.57 12.98L2.46 14.63C2.27 14.78 2.22 15.05 2.34 15.27L4.34 18.73C4.46 18.95 4.73 19.03 4.95 18.95L7.44 17.94C7.96 18.34 8.5 18.68 9.13 18.93L9.5 21.58C9.54 21.82 9.75 22 10 22H14C14.25 22 14.46 21.82 14.5 21.58L14.87 18.93C15.5 18.67 16.04 18.34 16.56 17.94L19.05 18.95C19.27 19.03 19.54 18.95 19.66 18.73L21.66 15.27C21.78 15.05 21.73 14.78 21.54 14.63L19.43 12.98Z"/>
                </svg>
                Subscription Actions
            </h4>

            <div class="action-buttons-grid">
                <!-- Cancel Subscription -->
                <button type="button" class="wcb-btn wcb-btn-outline cancel-subscription-btn"
                        data-child-id="<?php echo $child_id; ?>"
                        data-child-name="<?php echo esc_attr($child_user->display_name); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12S6.48 22 12 22 22 17.52 22 12 17.52 2 12 2ZM17 15.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59Z"/>
                    </svg>
                    Cancel Subscription
                </button>

                <!-- Stripe Customer Portal -->
                <button type="button" class="wcb-btn wcb-btn-primary stripe-portal-btn"
                        data-child-id="<?php echo $child_id; ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M14 3V5H17.59L7.76 14.83L9.17 16.24L19 6.41V10H21V3M12 2C6.48 2 2 6.48 2 12S6.48 22 12 22 22 17.52 22 12C22 10.74 21.79 9.53 21.38 8.4L19.8 9.98C19.93 10.64 20 11.31 20 12C20 16.41 16.41 20 12 20S4 16.41 4 12 7.59 4 12 4C12.69 4 13.36 4.07 14.02 4.2L15.6 2.62C14.47 2.21 13.26 2 12 2Z"/>
                    </svg>
                    Stripe Customer Portal
                </button>
            </div>
        </div>
    </div>
    <?php

    $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html,
            'title' => $child_user->display_name . ' - Manage Subscription'
        ]);

    } catch (Exception $e) {
        wp_send_json_error('Error loading subscription management: ' . $e->getMessage());
    }
}
add_action('wp_ajax_wcb_get_subscription_management', 'wcb_ajax_get_subscription_management');

/**
 * Remove Linked Child AJAX Handler
 */
function wcb_ajax_remove_linked_child() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wcb_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in');
        return;
    }

    $child_id = intval($_POST['child_id']);
    $parent_user_id = get_current_user_id();

    // Get current linked children
    $linked_children = get_user_meta($parent_user_id, 'wcb_linked_children', true);
    if (!is_array($linked_children)) {
        $linked_children = array();
    }

    // Check if child is actually linked
    if (!in_array($child_id, $linked_children)) {
        wp_send_json_error('Child is not linked to your account');
        return;
    }

    // Remove child from linked children array
    $linked_children = array_diff($linked_children, array($child_id));
    $linked_children = array_values($linked_children); // Re-index array

    // Update user meta
    $result = update_user_meta($parent_user_id, 'wcb_linked_children', $linked_children);

    if ($result !== false) {
        // Get child name for success message
        $child_user = get_user_by('ID', $child_id);
        $child_name = $child_user ? $child_user->display_name : 'Member';

        wp_send_json_success([
            'message' => $child_name . ' has been removed from your family dashboard.',
            'remaining_children' => count($linked_children)
        ]);
    } else {
        wp_send_json_error('Failed to remove member. Please try again.');
    }
}
add_action('wp_ajax_wcb_remove_linked_child', 'wcb_ajax_remove_linked_child');
