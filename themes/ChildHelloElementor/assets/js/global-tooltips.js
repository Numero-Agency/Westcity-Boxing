/**
 * Global Tooltip Enhancement for West City Boxing
 * Initialize modern tooltips site-wide
 */

(function($) {
    'use strict';
    
    function initializeTooltips() {
        // Enhanced jQuery UI tooltip options
        const tooltipOptions = {
            show: { 
                effect: "fadeIn", 
                duration: 200 
            },
            hide: { 
                effect: "fadeOut", 
                duration: 150 
            },
            position: {
                my: "center bottom-10",
                at: "center top",
                collision: "flipfit"
            },
            classes: {
                "ui-tooltip": "wcb-modern-tooltip"
            },
            content: function() {
                // Support for both title and data-tooltip attributes
                return $(this).attr('data-tooltip') || $(this).attr('title');
            }
        };
        
        // Initialize tooltips for elements with title or data-tooltip attributes
        $('[title], [data-tooltip]').each(function() {
            const $element = $(this);
            
            // Move title to data-tooltip to prevent browser default tooltip
            if ($element.attr('title') && !$element.attr('data-tooltip')) {
                $element.attr('data-tooltip', $element.attr('title'));
                $element.removeAttr('title');
            }
        });
        
        // Initialize jQuery UI tooltips
        $('[data-tooltip]').tooltip(tooltipOptions);
        
        // Also handle dynamically added elements
        $(document).on('mouseenter', '[title]:not([data-tooltip])', function() {
            const $this = $(this);
            if ($this.attr('title')) {
                $this.attr('data-tooltip', $this.attr('title'));
                $this.removeAttr('title');
                $this.tooltip(tooltipOptions);
                $this.tooltip('open');
            }
        });
        
        console.log('WCB Global tooltips initialized');
    }
    
    // Initialize on DOM ready
    $(document).ready(function() {
        initializeTooltips();
    });
    
    // Re-initialize after AJAX calls or dynamic content loading
    $(document).ajaxComplete(function() {
        setTimeout(initializeTooltips, 100);
    });
    
    // Global function to manually initialize tooltips on new elements
    window.wcbInitTooltips = initializeTooltips;
    
})(jQuery);