/**
 * Global Tooltip Enhancement for West City Boxing
 * Initialize modern tooltips site-wide
 */

(function($) {
    'use strict';
    
    // Simple throttle function
    $.throttle = function(delay, fn) {
        let timeoutId;
        let lastExecTime = 0;
        return function() {
            const context = this;
            const args = arguments;
            const currentTime = Date.now();
            
            if (currentTime - lastExecTime > delay) {
                fn.apply(context, args);
                lastExecTime = currentTime;
            } else {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(function() {
                    fn.apply(context, args);
                    lastExecTime = Date.now();
                }, delay - (currentTime - lastExecTime));
            }
        };
    };
    
    // Smart positioning function to detect edges
    function getSmartPosition(element) {
        const $element = $(element);
        const offset = $element.offset();
        const elementWidth = $element.outerWidth();
        const elementHeight = $element.outerHeight();
        const windowWidth = $(window).width();
        const windowHeight = $(window).height();
        const scrollTop = $(window).scrollTop();
        
        // Calculate distances from edges
        const distanceFromTop = offset.top - scrollTop;
        const distanceFromBottom = windowHeight - (distanceFromTop + elementHeight);
        const distanceFromLeft = offset.left;
        const distanceFromRight = windowWidth - (offset.left + elementWidth);
        
        // Default position (above element - better for action buttons)
        let my = "center bottom-10";
        let at = "center top";
        
        // If not enough space above, position below
        if (distanceFromTop < 50 && distanceFromBottom > 50) {
            my = "center top+10";
            at = "center bottom";
        }
        
        // If not enough horizontal space, adjust
        if (distanceFromLeft < 100) {
            my = my.replace("center", "left+10");
            at = at.replace("center", "left");
        } else if (distanceFromRight < 100) {
            my = my.replace("center", "right-10");
            at = at.replace("center", "right");
        }
        
        return { my: my, at: at };
    }
    
    function initializeTooltips() {
        // Enhanced jQuery UI tooltip options with better positioning
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
                my: "center top+10",
                at: "center bottom",
                collision: "flipfit flip",
                using: function(position, feedback) {
                    $(this).css(position);
                    
                    // Add dynamic class based on actual position
                    const tooltip = $(this);
                    tooltip.removeClass('tooltip-above tooltip-below tooltip-left tooltip-right');
                    
                    if (feedback.vertical === "top") {
                        tooltip.addClass('tooltip-above');
                    } else if (feedback.vertical === "bottom") {
                        tooltip.addClass('tooltip-below');
                    }
                    
                    if (feedback.horizontal === "left") {
                        tooltip.addClass('tooltip-left');
                    } else if (feedback.horizontal === "right") {
                        tooltip.addClass('tooltip-right');
                    }
                }
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
        
        // Initialize jQuery UI tooltips with smart positioning
        $('[data-tooltip]').each(function() {
            const smartPos = getSmartPosition(this);
            const elementOptions = $.extend({}, tooltipOptions, {
                position: $.extend({}, tooltipOptions.position, smartPos)
            });
            $(this).tooltip(elementOptions);
        });
        
        // Also handle dynamically added elements
        $(document).on('mouseenter', '[title]:not([data-tooltip])', function() {
            const $this = $(this);
            if ($this.attr('title')) {
                $this.attr('data-tooltip', $this.attr('title'));
                $this.removeAttr('title');
                
                // Use smart positioning for dynamic tooltips too
                const smartPos = getSmartPosition(this);
                const elementOptions = $.extend({}, tooltipOptions, {
                    position: $.extend({}, tooltipOptions.position, smartPos)
                });
                
                $this.tooltip(elementOptions);
                $this.tooltip('open');
            }
        });
        
        // Add viewport edge detection for CSS tooltips
        function updateTooltipClasses() {
            $('[data-tooltip]').each(function() {
                const $element = $(this);
                const offset = $element.offset();
                const elementWidth = $element.outerWidth();
                const elementHeight = $element.outerHeight();
                const windowWidth = $(window).width();
                const windowHeight = $(window).height();
                const scrollTop = $(window).scrollTop();
                
                // Calculate distances from edges
                const distanceFromTop = offset.top - scrollTop;
                const distanceFromBottom = windowHeight - (distanceFromTop + elementHeight);
                const distanceFromLeft = offset.left;
                const distanceFromRight = windowWidth - (offset.left + elementWidth);
                
                // Remove existing positioning classes
                $element.removeClass('near-top near-bottom near-left near-right');
                
                // Add appropriate classes based on position
                // Since default is above, only add near-top if very close to top
                if (distanceFromTop < 60) {
                    $element.addClass('near-top');
                }
                // near-bottom is less critical since that's our default position
                
                if (distanceFromLeft < 120) {
                    $element.addClass('near-left');
                } else if (distanceFromRight < 120) {
                    $element.addClass('near-right');
                }
            });
        }
        
        // Update classes on scroll and resize
        $(window).on('scroll resize', $.throttle(100, updateTooltipClasses));
        
        // Initial update
        setTimeout(updateTooltipClasses, 100);
        
        console.log('WCB Global tooltips initialized with smart positioning');
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