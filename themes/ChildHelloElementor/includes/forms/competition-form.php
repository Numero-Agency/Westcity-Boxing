<?php
/**
 * Competition Form (ACF Post Type Version)
 * Form for logging competition events as 'competition' post type with ACF fields
 */

function wcb_competition_form_shortcode() {
    // Handle form submission
    if (isset($_POST['submit_competition']) && wp_verify_nonce($_POST['competition_nonce'], 'submit_competition')) {
        $result = wcb_handle_competition_submission();
        if ($result['success']) {
            echo '<div class="form-success">✅ Competition logged successfully! <a href="' . esc_url(get_permalink($result['post_id'])) . '" target="_blank">View Competition</a></div>';
        } else {
            echo '<div class="form-error">❌ Error: ' . esc_html($result['message']) . '</div>';
        }
    }

    // Get only users with Competitive Team membership for student selection
    $competitive_team_id = 1932;
    $users = wcb_get_competitive_team_members();

    ob_start();
    ?>
    <div class="wcb-form-container">
        <div class="form-header">
            <h2><span class="dashicons dashicons-awards"></span> Competition Form</h2>
            <p>Log a new competition event below</p>
        </div>

        <?php if (empty($users)): ?>
            <div class="form-info">
                <p><strong>ℹ️ No Competitive Team Members Found</strong></p>
                <p>To log a competition, you need students who are part of the Competitive Team program. Please add students to the Competitive Team membership first.</p>
            </div>
        <?php endif; ?>

        <form method="post" class="competition-form"<?php echo empty($users) ? ' style="opacity: 0.6; pointer-events: none;"' : ''; ?>>
            <?php wp_nonce_field('submit_competition', 'competition_nonce'); ?>
            <div class="form-group">
                <label for="event_name">Event Name *</label>
                <input type="text" name="event_name" id="event_name" required>
            </div>
            <div class="form-group">
                <label for="event_date">Event Date *</label>
                <input type="date" name="event_date" id="event_date" required>
            </div>
            <div class="form-group">
                <label for="where_was_it_hosted">Where was it hosted? *</label>
                <input type="text" name="where_was_it_hosted" id="where_was_it_hosted" required>
            </div>
            <div class="form-group">
                <label for="student_involved">Student involved * <small>(Competitive Team members only)</small></label>
                <select name="student_involved" id="student_involved" required>
                    <?php if (empty($users)): ?>
                        <option value="">No competitive team members found</option>
                    <?php else: ?>
                        <option value="">Select competitive team student</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="who_else_attended">Who else attended?</label>
                <input type="text" name="who_else_attended" id="who_else_attended">
            </div>
            <div class="form-group">
                <label for="results_wins">Results Wins *</label>
                <input type="number" name="results_wins" id="results_wins" min="0" required>
            </div>
            <div class="form-group">
                <label for="results_lost">Results Lost *</label>
                <input type="number" name="results_lost" id="results_lost" min="0" required>
            </div>
            <div class="form-group">
                <label for="highlights">Highlights</label>
                <textarea name="highlights" id="highlights"></textarea>
            </div>
            <button type="submit" name="submit_competition" class="btn-primary">Log Competition</button>
        </form>
    </div>
    <style>
    .wcb-form-container { max-width: 700px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
    .form-header { text-align: center; margin-bottom: 30px; }
    .form-header h2 { margin: 0 0 10px 0; color: #2c3e50; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 2rem; }
    .form-header p { color: #fff; font-size: 1.1rem; margin: 0; }
    .competition-form { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); }
    .competition-form .form-group { margin-bottom: 20px; }
    .competition-form label { display: block; font-weight: bold; margin-bottom: 6px; }
    .competition-form input, .competition-form select, .competition-form textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
    .competition-form textarea { min-height: 80px; }
    .competition-form .btn-primary { background: #e74c3c; color: #fff; border: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; cursor: pointer; }
    .competition-form .btn-primary:hover { background: #c0392b; }
    .form-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
    .form-error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
    .form-info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #bee5eb; }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('wcb_competition_form', 'wcb_competition_form_shortcode');

function wcb_handle_competition_submission() {
    if (empty($_POST['event_name']) || empty($_POST['event_date']) || empty($_POST['where_was_it_hosted']) || empty($_POST['student_involved']) || !isset($_POST['results_wins']) || !isset($_POST['results_lost'])) {
        return ['success' => false, 'message' => 'Please fill in all required fields'];
    }

    $post_data = [
        'post_title' => sanitize_text_field($_POST['event_name']),
        'post_type' => 'competition',
        'post_status' => 'publish',
        'post_author' => get_current_user_id(),
    ];
    $post_id = wp_insert_post($post_data);
    if (is_wp_error($post_id)) {
        return ['success' => false, 'message' => 'Failed to create competition'];
    }

    // Save ACF fields
    update_field('event_name', sanitize_text_field($_POST['event_name']), $post_id);
    update_field('event_date', sanitize_text_field($_POST['event_date']), $post_id);
    update_field('where_was_it_hosted', sanitize_text_field($_POST['where_was_it_hosted']), $post_id);
    update_field('student_involved', intval($_POST['student_involved']), $post_id);
    update_field('who_else_attended', sanitize_text_field($_POST['who_else_attended']), $post_id);
    update_field('results_wins', intval($_POST['results_wins']), $post_id);
    update_field('results_lost', intval($_POST['results_lost']), $post_id);
    update_field('highlights', sanitize_textarea_field($_POST['highlights']), $post_id);

    return ['success' => true, 'post_id' => $post_id];
}

/**
 * Get users who have Competitive Team membership
 */
function wcb_get_competitive_team_members() {
    global $wpdb;

    $competitive_team_id = 1932;
    $txn_table = $wpdb->prefix . 'mepr_transactions';

    // Get users with active Competitive Team transactions
    $competitive_users = $wpdb->get_results($wpdb->prepare("
        SELECT DISTINCT u.ID, u.display_name, u.user_email
        FROM {$wpdb->users} u
        JOIN {$txn_table} t ON u.ID = t.user_id
        WHERE t.product_id = %d
        AND t.status IN ('confirmed', 'complete')
        AND (t.expires_at IS NULL OR t.expires_at > NOW() OR t.expires_at = '0000-00-00 00:00:00')
        AND u.user_login != 'bwgdev'
        ORDER BY u.display_name
    ", $competitive_team_id));

    return $competitive_users;
}