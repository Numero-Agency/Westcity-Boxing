<?php
/**
 * Let admins add an additional membership (e.g. the free "Competitive Team")
 * from a member's existing subscription row.
 *
 * MemberPress's "Add Transaction" link on a subscription row pre-fills the
 * New Transaction form with that subscription's ID. If the admin then picks a
 * different membership, MemberPress rejects the save with:
 *   "This is not a subscription for membership 'X' but for 'Y'".
 *
 * When a NEW transaction is posted with a membership that differs from the
 * linked subscription's membership, the admin is adding a separate membership,
 * so we drop the subscription link before MemberPress validates. The
 * transaction is then saved as a standalone membership for that member and
 * the existing subscription is left untouched.
 *
 * Editing existing transactions is not affected.
 */

if (!defined('ABSPATH')) {
    exit;
}

function wcb_mepr_detach_mismatched_subscription() {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return;
    }

    if (($_GET['page'] ?? '') !== 'memberpress-trans' || ($_REQUEST['action'] ?? '') !== 'new') {
        return;
    }

    if (empty($_POST['subscr_num']) || empty($_POST['product_id']) || !class_exists('MeprSubscription')) {
        return;
    }

    // MemberPress runs its own capability and nonce checks on this request.
    // We only decide whether the subscription link should be kept.
    if (!current_user_can('manage_options') && !current_user_can('remove_users')) {
        return;
    }

    $sub = MeprSubscription::get_one_by_subscr_id(sanitize_text_field(wp_unslash($_POST['subscr_num'])));

    if (!$sub || (int) $sub->product_id === (int) $_POST['product_id']) {
        return;
    }

    unset($_POST['subscr_num'], $_REQUEST['subscr_num']);
}
add_action('admin_init', 'wcb_mepr_detach_mismatched_subscription', 1);
