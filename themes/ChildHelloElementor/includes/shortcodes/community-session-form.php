<?php
// Standalone Community Session Form Shortcode

function wcb_community_session_form_shortcode($atts) {
    $atts = shortcode_atts([
        'title' => 'Add Community Class Session',
        'button_text' => 'Add Session',
        'trigger_text' => 'Add Session',
        'class' => 'wcb-community-session-form',
        'show_trigger' => 'true',
        'display' => 'modal' // new attribute
    ], $atts);
    
    $is_inline = (strtolower($atts['display']) === 'inline');
    ob_start();
    ?>
    <div class="<?php echo esc_attr($atts['class']); ?>">
        <?php if(!$is_inline && $atts['show_trigger'] === 'true'): ?>
        <button class="btn-add-session" id="add-session-btn">
            <span class="dashicons dashicons-plus"></span> <?php echo esc_html($atts['trigger_text']); ?>
        </button>
        <?php endif; ?>
        
        <?php if($is_inline): ?>
            <div class="wcb-form-container">
                <div class="form-header">
                    <h2><span class="dashicons dashicons-groups"></span> <?php echo esc_html($atts['title']); ?></h2>
                    <p>Record a community class session and attendance</p>
                </div>
                <form id="add-session-form" class="wcb-session-form">
                    <?php wp_nonce_field('add_community_session', 'community_session_nonce'); ?>
                    <div class="form-row">
                        <label for="session_date">Date & Time *</label>
                        <input type="datetime-local" id="session_date" name="session_date" required>
                    </div>
                    <div class="form-row">
                        <label for="instructor">Instructor</label>
                        <select id="instructor" name="instructor">
                            <option value="">Select Instructor</option>
                            <?php
                            $instructors = get_users(['role__in' => ['administrator', 'editor', 'instructor']]);
                            foreach ($instructors as $instructor): ?>
                                <option value="<?php echo $instructor->ID; ?>">
                                    <?php echo esc_html($instructor->display_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Attendance</label>
                        <div class="attendance-section">
                            <p class="field-description">Select members who attended this session:</p>
                            <div class="checkbox-grid" id="attendance-list">
                                <!-- Members loaded via AJAX -->
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <label for="session_notes">Notes</label>
                        <textarea id="session_notes" name="session_notes" rows="4" 
                            placeholder="Any notes about this session..."></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-submit"><?php echo esc_html($atts['button_text']); ?></button>
                        <button type="button" class="btn-cancel" id="cancel-session">Cancel</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Modal version -->
            <div id="add-session-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><span class="dashicons dashicons-plus"></span> <?php echo esc_html($atts['title']); ?></h3>
                        <button class="close-modal" id="close-session-modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="add-session-form">
                            <?php wp_nonce_field('add_community_session', 'community_session_nonce'); ?>
                            <div class="form-row">
                                <label for="session_date">Date & Time *</label>
                                <input type="datetime-local" id="session_date" name="session_date" required>
                            </div>
                            <div class="form-row">
                                <label for="instructor">Instructor</label>
                                <select id="instructor" name="instructor">
                                    <option value="">Select Instructor</option>
                                    <?php
                                    $instructors = get_users(['role__in' => ['administrator', 'editor', 'instructor']]);
                                    foreach ($instructors as $instructor): ?>
                                        <option value="<?php echo $instructor->ID; ?>">
                                            <?php echo esc_html($instructor->display_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-row">
                                <label>Attendance</label>
                                <div class="attendance-section">
                                    <p class="field-description">Select members who attended this session:</p>
                                    <div class="checkbox-grid" id="attendance-list">
                                        <!-- Members loaded via AJAX -->
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <label for="session_notes">Notes</label>
                                <textarea id="session_notes" name="session_notes" rows="4" 
                                    placeholder="Any notes about this session..."></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn-submit"><?php echo esc_html($atts['button_text']); ?></button>
                                <button type="button" class="btn-cancel" id="cancel-session">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var isInline = <?php echo $is_inline ? 'true' : 'false'; ?>;
        if(isInline) {
            loadMembersForAttendance();
            // Cancel button just resets the form
            $('#cancel-session').on('click', function() {
                $('#add-session-form')[0].reset();
            });
        } else {
            // Modal logic
            $('#add-session-btn').on('click', function() {
                loadMembersForAttendance();
                $('#add-session-modal').show();
            });
            $('#close-session-modal, #cancel-session').on('click', function() {
                $('#add-session-modal').hide();
            });
        }
        // Add Session Form
        $('#add-session-form').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: formData + '&action=wcb_add_community_session&nonce=' + wcb_ajax.nonce,
                success: function(response) {
                    if (response.success) {
                        if(isInline) {
                            $('#add-session-form')[0].reset();
                        } else {
                            $('#add-session-modal').hide();
                            $('#add-session-form')[0].reset();
                        }
                        alert('Session added successfully!');
                        $(document).trigger('session_added', [response.data]);
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('Failed to add session. Please try again.');
                }
            });
        });
        function loadMembersForAttendance() {
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_load_members_for_attendance',
                    nonce: wcb_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#attendance-list').html(response.data);
                    } else {
                        $('#attendance-list').html('<p class="error">Failed to load members</p>');
                    }
                },
                error: function() {
                    $('#attendance-list').html('<p class="error">Failed to load members</p>');
                }
            });
        }
    });
    </script>
    
    <style>
    /* Modal Styles */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: white;
        border-radius: 8px;
        width: 90%;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .modal-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-header h3 {
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .close-modal {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #999;
    }
    
    .modal-body {
        padding: 20px;
    }
    /* Remove all form and container styles for inline, as they are handled by forms.css */
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode('wcb_community_session_form', 'wcb_community_session_form_shortcode'); 