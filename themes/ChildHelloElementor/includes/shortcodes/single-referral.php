<?php
/**
 * Single Referral Display Component
 * Displays a single referral record with all details
 */

function single_referral_shortcode($atts) {
    try {
        $atts = shortcode_atts([
            'referral_id' => '',
            'class' => 'wcb-single-referral',
            'show_edit_link' => 'true'
        ], $atts);
    
    // Get referral ID from URL parameter, post ID, or shortcode attribute
    $referral_id = $atts['referral_id'];
    if (empty($referral_id)) {
        $referral_id = isset($_GET['referral_id']) ? intval($_GET['referral_id']) : 0;
    }
    if (empty($referral_id)) {
        $referral_id = get_the_ID();
    }
    
    if (empty($referral_id)) {
        return '<div class="error">No referral ID provided</div>';
    }
    
    // Get referral post
    $referral_post = get_post($referral_id);
    if (!$referral_post || $referral_post->post_type !== 'referral') {
        return '<div class="error">Referral not found or invalid post type</div>';
    }
    
    // Get referral data from ACF fields with fallbacks
    if (function_exists('get_field')) {
        $first_name = get_field('first_name', $referral_id);
        $last_name = get_field('last_name', $referral_id);
        $date_of_birth = get_field('date_of_birth', $referral_id);
        $ethnicity = get_field('ethnicity', $referral_id);
        $gender = get_field('gender', $referral_id);
        $contact_phone = get_field('contact_phone', $referral_id);
        $contact_email = get_field('contact_email', $referral_id);
    } else {
        $first_name = get_post_meta($referral_id, 'first_name', true);
        $last_name = get_post_meta($referral_id, 'last_name', true);
        $date_of_birth = get_post_meta($referral_id, 'date_of_birth', true);
        $ethnicity = get_post_meta($referral_id, 'ethnicity', true);
        $gender = get_post_meta($referral_id, 'gender', true);
        $contact_phone = get_post_meta($referral_id, 'contact_phone', true);
        $contact_email = get_post_meta($referral_id, 'contact_email', true);
    }
    
    // Get all other fields with the same safe approach
    if (function_exists('get_field')) {
        // Parent/Guardian details
        $parent_name = get_field('parent_name', $referral_id);
        $parent_phone = get_field('parent_phone', $referral_id);
        $parent_email = get_field('parent_email', $referral_id);
        
        // Medical & Address
        $medical_information = get_field('medical_information', $referral_id);
        $address = get_field('address', $referral_id);
        $suburb = get_field('suburb', $referral_id);
        
        // Family & Safety
        $whanau_history = get_field('whanau_history', $referral_id);
        $protective_factors = get_field('protective_factors', $referral_id);
        $staff_safety_check = get_field('staff_safety_check', $referral_id);
        $people_in_home = get_field('people_in_home', $referral_id);
        $risk_factors = get_field('risk_factors', $referral_id);
        
        // Referrer Information
        $referrer_name = get_field('referrer_name', $referral_id);
        $agency = get_field('agency', $referral_id);
        $referrer_contact = get_field('referrer_contact', $referral_id);
        $other_agencies = get_field('other_agencies', $referral_id);
        
        // Additional Information
        $notes = get_field('notes', $referral_id);
        $referral_date = get_field('referral_date', $referral_id);
        $referral_status = get_field('referral_status', $referral_id) ?: 'pending';
    } else {
        // Fallback to post meta
        $parent_name = get_post_meta($referral_id, 'parent_name', true);
        $parent_phone = get_post_meta($referral_id, 'parent_phone', true);
        $parent_email = get_post_meta($referral_id, 'parent_email', true);
        $medical_information = get_post_meta($referral_id, 'medical_information', true);
        $address = get_post_meta($referral_id, 'address', true);
        $suburb = get_post_meta($referral_id, 'suburb', true);
        $whanau_history = get_post_meta($referral_id, 'whanau_history', true);
        $protective_factors = get_post_meta($referral_id, 'protective_factors', true);
        $staff_safety_check = get_post_meta($referral_id, 'staff_safety_check', true);
        $people_in_home = get_post_meta($referral_id, 'people_in_home', true);
        $risk_factors = get_post_meta($referral_id, 'risk_factors', true);
        $referrer_name = get_post_meta($referral_id, 'referrer_name', true);
        $agency = get_post_meta($referral_id, 'agency', true);
        $referrer_contact = get_post_meta($referral_id, 'referrer_contact', true);
        $other_agencies = get_post_meta($referral_id, 'other_agencies', true);
        $notes = get_post_meta($referral_id, 'notes', true);
        $referral_date = get_post_meta($referral_id, 'referral_date', true);
        $referral_status = get_post_meta($referral_id, 'referral_status', true) ?: 'pending';
    }
    
    // Calculate age if DOB is available - with error handling
    $age = '';
    if ($date_of_birth && !empty(trim($date_of_birth))) {
        try {
            // Validate date format first
            $date_string = trim($date_of_birth);
            if (strtotime($date_string) !== false) {
                $dob = new DateTime($date_string);
                $now = new DateTime();
                $age_diff = $now->diff($dob);
                $age = $age_diff->y;
            }
        } catch (Exception $e) {
            // Log error for debugging but don't break the page
            error_log('Invalid date format in referral ' . $referral_id . ': ' . $date_of_birth);
            $age = '';
        }
    }
    
    // Format dates safely
    $formatted_referral_date = 'Unknown Date';
    if ($referral_date && !empty(trim($referral_date))) {
        $timestamp = strtotime($referral_date);
        if ($timestamp !== false) {
            $formatted_referral_date = date('l, F j, Y', $timestamp);
        }
    }
    
    $formatted_dob = 'Unknown';
    if ($date_of_birth && !empty(trim($date_of_birth))) {
        $timestamp = strtotime($date_of_birth);
        if ($timestamp !== false) {
            $formatted_dob = date('F j, Y', $timestamp);
        }
    }
    
    // Get creator info
    $creator = get_user_by('ID', $referral_post->post_author);
    $creator_name = $creator ? $creator->display_name : 'Unknown';
    
    // Full name
    $full_name = trim($first_name . ' ' . $last_name);
    
    ob_start();
    ?>
    <div class="<?php echo esc_attr($atts['class']); ?>" id="single-referral-<?php echo $referral_id; ?>">
        <!-- Referral Header -->
        <div class="referral-header">
            <div class="referral-title-section">
                <h1 class="referral-title">Referral: <?php echo esc_html($full_name); ?></h1>
                <div class="referral-meta">
                    <span class="referral-date"><span class="dashicons dashicons-calendar-alt"></span> <?php echo esc_html($formatted_referral_date); ?></span>
                    <?php if ($age): ?>
                        <span class="young-person-age"><span class="dashicons dashicons-admin-users"></span> <?php echo esc_html($age); ?> years old</span>
                    <?php endif; ?>
                    <span class="referral-badge status-<?php echo esc_attr($referral_status); ?>">
                        <span class="dashicons dashicons-flag"></span> <?php echo esc_html(ucfirst($referral_status)); ?>
                    </span>
                </div>
            </div>
            
            <?php if ($atts['show_edit_link'] === 'true' && current_user_can('edit_posts')): ?>
            <div class="referral-actions">
                <a href="/staff-dashboard/" class="btn-dashboard-modern">Dashboard</a>
                <a href="<?php echo admin_url('post.php?post=' . $referral_id . '&action=edit'); ?>" class="btn-edit-modern">Edit Referral</a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Referral Content -->
        <div class="referral-content">
            <!-- Young Person Details Section -->
            <div class="referral-section young-person-section">
                <h3 class="section-title"><span class="dashicons dashicons-admin-users"></span> Young Person Details</h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Full Name:</span>
                        <span class="detail-value"><?php echo esc_html($full_name); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Date of Birth:</span>
                        <span class="detail-value"><?php echo esc_html($formatted_dob); ?> <?php echo $age ? '(' . $age . ' years old)' : ''; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Ethnicity:</span>
                        <span class="detail-value"><?php echo esc_html($ethnicity ?: 'Not specified'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Gender:</span>
                        <span class="detail-value"><?php echo esc_html($gender ?: 'Not specified'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">
                            <?php if ($contact_phone): ?>
                                <a href="tel:<?php echo esc_attr($contact_phone); ?>"><?php echo esc_html($contact_phone); ?></a>
                            <?php else: ?>
                                Not provided
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">
                            <?php if ($contact_email): ?>
                                <a href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>
                            <?php else: ?>
                                Not provided
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Parent/Guardian Details Section -->
            <div class="referral-section parent-section">
                <h3 class="section-title"><span class="dashicons dashicons-groups"></span> Parent/Guardian Details</h3>
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Name:</span>
                        <span class="detail-value"><?php echo esc_html($parent_name ?: 'Not provided'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">
                            <?php if ($parent_phone): ?>
                                <a href="tel:<?php echo esc_attr($parent_phone); ?>"><?php echo esc_html($parent_phone); ?></a>
                            <?php else: ?>
                                Not provided
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">
                            <?php if ($parent_email): ?>
                                <a href="mailto:<?php echo esc_attr($parent_email); ?>"><?php echo esc_html($parent_email); ?></a>
                            <?php else: ?>
                                Not provided
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Address & Medical Information Section -->
            <div class="referral-section address-medical-section">
                <h3 class="section-title"><span class="dashicons dashicons-location"></span> Address & Medical Information</h3>
                <div class="details-grid">
                    <div class="detail-item wide">
                        <span class="detail-label">Address:</span>
                        <span class="detail-value"><?php echo nl2br(esc_html($address ?: 'Not provided')); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Suburb:</span>
                        <span class="detail-value"><?php echo esc_html($suburb ?: 'Not provided'); ?></span>
                    </div>
                    <?php if ($medical_information): ?>
                    <div class="detail-item wide">
                        <span class="detail-label">Medical Information:</span>
                        <span class="detail-value medical-info"><?php echo nl2br(esc_html($medical_information)); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Family & Safety Information Section -->
            <?php if ($whanau_history || $protective_factors || $staff_safety_check || $people_in_home || $risk_factors): ?>
            <div class="referral-section family-safety-section">
                <h3 class="section-title"><span class="dashicons dashicons-shield"></span> Family & Safety Information</h3>
                <div class="content-blocks">
                    <?php if ($whanau_history): ?>
                    <div class="content-block">
                        <h4>Whanau History</h4>
                        <div class="content-text"><?php echo nl2br(esc_html($whanau_history)); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($protective_factors): ?>
                    <div class="content-block positive">
                        <h4>Protective Factors</h4>
                        <div class="content-text"><?php echo nl2br(esc_html($protective_factors)); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($risk_factors): ?>
                    <div class="content-block warning">
                        <h4>Risk Factors</h4>
                        <div class="content-text"><?php echo nl2br(esc_html($risk_factors)); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($staff_safety_check): ?>
                    <div class="content-block important">
                        <h4>Staff Safety Check</h4>
                        <div class="content-text"><?php echo nl2br(esc_html($staff_safety_check)); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($people_in_home): ?>
                    <div class="content-block">
                        <h4>People Living in the Home</h4>
                        <div class="content-text"><?php echo nl2br(esc_html($people_in_home)); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Referrer Information Section -->
            <?php if ($referrer_name || $agency || $referrer_contact || $other_agencies): ?>
            <div class="referral-section referrer-section">
                <h3 class="section-title"><span class="dashicons dashicons-businessman"></span> Referrer Information</h3>
                <div class="details-grid">
                    <?php if ($referrer_name): ?>
                    <div class="detail-item">
                        <span class="detail-label">Referrer Name:</span>
                        <span class="detail-value"><?php echo esc_html($referrer_name); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($agency): ?>
                    <div class="detail-item">
                        <span class="detail-label">Agency:</span>
                        <span class="detail-value"><?php echo esc_html($agency); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($referrer_contact): ?>
                    <div class="detail-item wide">
                        <span class="detail-label">Referrer Contact:</span>
                        <span class="detail-value"><?php echo nl2br(esc_html($referrer_contact)); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($other_agencies): ?>
                    <div class="detail-item wide">
                        <span class="detail-label">Other Agencies Involved:</span>
                        <span class="detail-value"><?php echo nl2br(esc_html($other_agencies)); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Additional Notes Section -->
            <?php if ($notes): ?>
            <div class="referral-section notes-section">
                <h3 class="section-title"><span class="dashicons dashicons-edit"></span> Additional Notes</h3>
                <div class="notes-content">
                    <?php echo nl2br(esc_html($notes)); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Referral Meta Info -->
            <div class="referral-section meta-section">
                <h3 class="section-title"><span class="dashicons dashicons-info"></span> Referral Details</h3>
                <div class="meta-grid">
                    <div class="meta-item">
                        <span class="meta-label">Status:</span>
                        <span class="meta-value status-<?php echo esc_attr($referral_status); ?>">
                            <?php echo esc_html(ucfirst($referral_status)); ?>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Submitted by:</span>
                        <span class="meta-value"><?php echo esc_html($creator_name); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Created:</span>
                        <span class="meta-value"><?php echo esc_html(date('F j, Y g:i A', strtotime($referral_post->post_date))); ?></span>
                    </div>
                    <?php if ($referral_post->post_modified !== $referral_post->post_date): ?>
                    <div class="meta-item">
                        <span class="meta-label">Last updated:</span>
                        <span class="meta-value"><?php echo esc_html(date('F j, Y g:i A', strtotime($referral_post->post_modified))); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    /* Modern Minimalistic Black & White Styles - Matching Class Session */
    .wcb-single-referral {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .referral-header {
        background: #000000;
        color: white;
        padding: 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        border-bottom: 1px solid #e5e5e5;
    }
    
    .referral-title-section {
        flex: 1;
    }
    
    .referral-title {
        font-size: 28px!important;
        font-weight: 600;
        color: white !important;
        margin: 0 0 10px 0;
        line-height: 1.2;
    }
    
    .referral-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 10px;
    }
    
    .referral-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #cccccc;
        font-size: 14px;
    }
    
    .referral-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 1px solid #e5e5e5;
        background: white;
    }
    
    .referral-badge.status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .referral-badge.status-reviewed {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .referral-badge.status-processed {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    .referral-badge.status-contacted {
        background: #e2e3e5;
        color: #383d41;
        border: 1px solid #d6d8db;
    }
    
    .referral-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .btn-dashboard-modern,
    .btn-edit-modern {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: background-color 0.3s ease;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .btn-dashboard-modern {
        background: white;
        color: #000000;
        border: 1px solid #e5e5e5;
    }
    
    .btn-dashboard-modern:hover {
        background: #f8f9fa;
        color: #000000;
        text-decoration: none;
    }
    
    .btn-edit-modern {
        background: white;
        color: #000000;
        border: 1px solid #e5e5e5;
    }
    
    .btn-edit-modern:hover {
        background: #f8f9fa;
        color: #000000;
        text-decoration: none;
    }
    
    .referral-content {
        display: flex;
        flex-direction: column;
        gap: 30px;
        padding: 24px;
    }
    
    .referral-section {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .section-title {
        background: #f8f9fa;
        color: #000000;
        padding: 16px 20px;
        margin: 0;
        text-align: left;
        font-weight: 700;
        border-bottom: 2px solid #e5e5e5;
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
        font-size: 14px !important;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .section-title .dashicons {
        font-size: 14px;
        color: #666666;
    }
    
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 16px;
        padding: 20px;
    }
    
    .detail-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        transition: background-color 0.2s ease;
    }
    
    .detail-item.wide {
        grid-column: 1 / -1;
    }
    
    .detail-item:hover {
        background: #f0f0f0;
    }
    
    .detail-label {
        font-size: 12px;
        color: #666666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
        margin-bottom: 2px;
    }
    
    .detail-value {
        color: #000000;
        font-weight: 500;
        font-size: 14px;
        line-height: 1.4;
        word-break: break-word;
    }
    
    .detail-value.medical-info {
        background: white;
        padding: 16px;
        border-radius: 6px;
        border: 1px solid #e5e5e5;
        color: #333333;
        font-size: 14px;
        line-height: 1.6;
    }
    
    .detail-value a {
        color: #000000;
        text-decoration: none;
        border-bottom: 1px solid #e5e5e5;
    }
    
    .detail-value a:hover {
        border-bottom-color: #000000;
    }
    
    .content-blocks {
        display: flex;
        flex-direction: column;
        gap: 20px;
        padding: 20px;
    }
    
    .content-block {
        background: white;
        padding: 20px;
        border-radius: 6px;
        border: 1px solid #e5e5e5;
    }
    
    .content-block.positive {
        border: 1px solid #e5e5e5;
    }
    
    .content-block.warning {
        border: 1px solid #e5e5e5;
    }
    
    .content-block.important {
        border: 1px solid #e5e5e5;
    }
    
    .content-block h4 {
        margin: 0 0 10px 0;
        color: #000000;
        font-size: 16px;
        font-weight: 600;
    }
    
    .content-text {
        color: #333333;
        line-height: 1.5;
    }
    
    .notes-content {
        background: white;
        padding: 20px;
        border-radius: 6px;
        border: 1px solid #e5e5e5;
        color: #333333;
        line-height: 1.5;
    }
    
    .meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        padding: 20px;
    }
    
    .meta-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-radius: 6px;
        transition: background-color 0.2s ease;
    }
    
    .meta-item:hover {
        background: #f0f0f0;
    }
    
    .meta-label {
        font-size: 12px;
        color: #666666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
    }
    
    .meta-value {
        color: #000000;
        font-weight: 500;
        font-size: 14px;
    }
    
    .meta-value.status-pending { color: #856404; font-weight: bold; }
    .meta-value.status-reviewed { color: #155724; font-weight: bold; }
    .meta-value.status-processed { color: #0c5460; font-weight: bold; }
    .meta-value.status-contacted { color: #383d41; font-weight: bold; }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .wcb-single-referral {
            margin: 10px;
        }
        
        .referral-header {
            flex-direction: column;
            gap: 15px;
            align-items: stretch;
            padding: 20px;
        }
        
        .referral-title {
            font-size: 24px;
        }
        
        .referral-actions {
            justify-content: stretch;
        }
        
        .btn-dashboard-modern,
        .btn-edit-modern {
            flex: 1;
            justify-content: center;
        }
        
        .referral-content {
            padding: 16px;
        }
        
        .section-title {
            font-size: 12px !important;
            padding: 12px 16px;
        }
        
        .details-grid {
            grid-template-columns: 1fr;
            padding: 16px;
        }
        
        .meta-grid {
            grid-template-columns: 1fr;
            padding: 16px;
        }
        
        .meta-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
        
        .content-blocks {
            padding: 16px;
        }
    }
    </style>
    
    <?php
    return ob_get_clean();
    
    } catch (Exception $e) {
        error_log('Single Referral Shortcode Error: ' . $e->getMessage());
        return '<div class="wcb-error-message">
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin: 20px; border: 1px solid #f5c6cb;">
                <h3>Error Loading Referral</h3>
                <p>There was an error displaying this referral. Please check the error logs or contact support.</p>
                <p><strong>Error:</strong> ' . esc_html($e->getMessage()) . '</p>
            </div>
        </div>';
    }
}
add_shortcode('single_referral', 'single_referral_shortcode'); 