/**
 * Family Registration Form JavaScript
 * Handles form interactions and AJAX submissions
 */

jQuery(document).ready(function($) {
    
    // Add Child Form Submission
    $('#add-child-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> Adding...');
        
        // Get form data
        const formData = {
            action: 'wcb_add_family_child',
            nonce: wcb_ajax.nonce,
            child_name: $('#child_name').val(),
            child_age: $('#child_age').val(),
            child_email: $('#child_email').val(),
            program_group: $('#program_group').val()
        };
        
        // Submit via AJAX
        $.ajax({
            url: wcb_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showNotice('Child added successfully!', 'success');
                    
                    // Reset form
                    form[0].reset();
                    
                    // Reload page to show updated children list
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotice('Error: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('Failed to add child. Please try again.', 'error');
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Edit Child Button Click
    $('.edit-child').on('click', function() {
        const childId = $(this).data('child-id');
        
        // Get child details via AJAX
        $.ajax({
            url: wcb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wcb_get_family_child_details',
                nonce: wcb_ajax.nonce,
                child_id: childId
            },
            success: function(response) {
                if (response.success) {
                    const child = response.data.child;
                    
                    // Populate edit form
                    $('#edit_child_id').val(child.id);
                    $('#edit_child_name').val(child.name);
                    $('#edit_child_age').val(child.age || '');
                    $('#edit_child_email').val(child.email || '');
                    $('#edit_program_group').val(child.program_group);
                    $('#edit_child_status').val(child.status);
                    
                    // Show modal
                    $('#edit-child-modal').show();
                } else {
                    showNotice('Error: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('Failed to load child details.', 'error');
            }
        });
    });
    
    // Edit Child Form Submission
    $('#edit-child-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).html('Updating...');
        
        // Get form data
        const formData = {
            action: 'wcb_edit_family_child',
            nonce: wcb_ajax.nonce,
            child_id: $('#edit_child_id').val(),
            child_name: $('#edit_child_name').val(),
            child_age: $('#edit_child_age').val(),
            child_email: $('#edit_child_email').val(),
            program_group: $('#edit_program_group').val(),
            child_status: $('#edit_child_status').val()
        };
        
        // Submit via AJAX
        $.ajax({
            url: wcb_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showNotice('Child updated successfully!', 'success');
                    
                    // Hide modal
                    $('#edit-child-modal').hide();
                    
                    // Reload page to show updated children list
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotice('Error: ' + response.data, 'error');
                }
            },
            error: function() {
                showNotice('Failed to update child. Please try again.', 'error');
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Remove Child Button Click
    $('.remove-child').on('click', function() {
        const childId = $(this).data('child-id');
        const childCard = $(this).closest('.child-card');
        const childName = childCard.find('h4').text();
        
        if (confirm('Are you sure you want to remove ' + childName + ' from your family membership?')) {
            const button = $(this);
            const originalText = button.html();
            
            // Show loading
            button.prop('disabled', true).html('Removing...');
            
            // Submit via AJAX
            $.ajax({
                url: wcb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wcb_remove_family_child',
                    nonce: wcb_ajax.nonce,
                    child_id: childId
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        showNotice('Child removed successfully!', 'success');
                        
                        // Remove child card with animation
                        childCard.fadeOut(300, function() {
                            $(this).remove();
                            
                            // Reload page to update counts
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        });
                    } else {
                        showNotice('Error: ' + response.data, 'error');
                        button.prop('disabled', false).html(originalText);
                    }
                },
                error: function() {
                    showNotice('Failed to remove child. Please try again.', 'error');
                    button.prop('disabled', false).html(originalText);
                }
            });
        }
    });
    
    // Modal Close Functionality
    $('.modal-close').on('click', function() {
        $('#edit-child-modal').hide();
    });
    
    // Close modal when clicking outside
    $('#edit-child-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    // Close modal on Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('#edit-child-modal').hide();
        }
    });
    
    // Form Validation
    function validateForm(form) {
        let isValid = true;
        
        // Check required fields
        form.find('[required]').each(function() {
            const field = $(this);
            const value = field.val().trim();
            
            if (!value) {
                field.addClass('error');
                isValid = false;
            } else {
                field.removeClass('error');
            }
        });
        
        return isValid;
    }
    
    // Real-time validation
    $('input[required], select[required]').on('blur', function() {
        const field = $(this);
        const value = field.val().trim();
        
        if (!value) {
            field.addClass('error');
        } else {
            field.removeClass('error');
        }
    });
    
    // Show notification function
    function showNotice(message, type) {
        // Remove existing notices
        $('.wcb-notice-dynamic').remove();
        
        // Create notice element
        const noticeClass = type === 'success' ? 'wcb-notice-success' : 'wcb-notice-error';
        const notice = $('<div class="wcb-notice wcb-notice-dynamic ' + noticeClass + '">' + message + '</div>');
        
        // Add to top of form
        $('.wcb-family-registration').prepend(notice);
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
        
        // Scroll to notice
        $('html, body').animate({
            scrollTop: notice.offset().top - 20
        }, 300);
    }
    
    // Age-based program suggestions
    $('#child_age').on('change', function() {
        const age = parseInt($(this).val());
        const programSelect = $('#program_group');
        
        if (age) {
            // Clear current selection
            programSelect.val('');
            
            // Suggest appropriate programs based on age
            let suggestedProgram = '';
            
            if (age >= 3 && age <= 6) {
                suggestedProgram = 'mini_cadet_boys'; // or girls based on gender
            } else if (age >= 7 && age <= 12) {
                suggestedProgram = 'cadet_boys_1'; // or girls
            } else if (age >= 13 && age <= 18) {
                suggestedProgram = 'youth_boys_1'; // or girls
            }
            
            // Highlight suggested options
            programSelect.find('option').removeClass('suggested');
            if (suggestedProgram) {
                programSelect.find('option[value*="' + suggestedProgram.split('_')[0] + '"]').addClass('suggested');
            }
        }
    });
    
});

// Add CSS for form validation
const validationCSS = `
<style>
.wcb-form input.error,
.wcb-form select.error {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.wcb-notice-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.wcb-notice-dynamic {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.wcb-form option.suggested {
    background-color: #fff3cd;
    font-weight: bold;
}
</style>
`;

// Inject CSS
jQuery('head').append(validationCSS);
