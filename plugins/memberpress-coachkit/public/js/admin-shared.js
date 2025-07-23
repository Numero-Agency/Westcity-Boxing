(function ($) {
  $(document).ready(function () {

    /**
     * Add events and perform actions that need to run immediately
     */
    function initialize() {
      $(document).on('change', '[data-submit-query]', addQueryToURL)
      $(document).on('click', '.accordion__title', toggleAccordion)

      // conditional fields
      initConditionalFields();
    }

    function addQueryToURL() {
      var selectedValue = $(this).val();
      var currentURL = window.location.href;

      if (selectedValue) {
        var newURL = addURLParameter(currentURL, $(this).data('submit-query'), selectedValue);
        window.location.href = newURL;
      } else {
        // Remove the item parameter from the URL
        var newURL = removeURLParameter(currentURL, $(this).data('submit-query'));
        window.location.href = newURL;
      }
    }

    function toggleAccordion(e) {
      e.preventDefault();
      var $this = $(this);

      if (!$this.hasClass("accordion-active")) {
        $(".accordion__content").slideUp(400);
        $(".accordion__title").removeClass("accordion-active");
        $('.accordion__arrow').removeClass('accordion__rotate');
      }

      $this.toggleClass("accordion-active");
      $this.next().slideToggle();
      $('.accordion__arrow', this).toggleClass('accordion__rotate');
    }

    function addURLParameter(url, param, value) {
      var separator = url.indexOf('?') !== -1 ? '&' : '?';
      return url + separator + param + '=' + value;
    }

    function removeURLParameter(url, param) {
      var urlParts = url.split('?');

      if (urlParts.length >= 2) {
        var prefix = encodeURIComponent(param) + '=';
        var queryStrings = urlParts[1].split('&');

        for (var i = queryStrings.length - 1; i >= 0; i--) {
          if (queryStrings[i].lastIndexOf(prefix, 0) !== -1) {
            queryStrings.splice(i, 1);
          }
        }

        if (queryStrings.length > 0) {
          return urlParts[0] + '?' + queryStrings.join('&');
        } else {
          return urlParts[0];
        }
      } else {
        return url;
      }
    }


    /**
     * Small utility function to show/hide fields conditionally.
     * Takes care of most of the use cases
     */
    function initConditionalFields() {
      $('[data-condition-field]').each(function () {
        var $element = $(this);
        var fieldName = $(this).data('condition-field');
        var $field = $(`[name='${fieldName}']`);
        var condition = $(this).data('condition');

        var toggleFields = function () {
          switch ($field.prop('type')) {
            case 'checkbox':
              $field.is(':checked') ? $element.show() : $element.hide();
              break;

            case 'radio':
              $field.filter(":checked").val() == condition ? $element.show() : $element.hide();
              break;

            default:
              $field.val() == condition ? $element.show() : $element.hide();
              break;
          }
        }
        toggleFields();

        $field.on('change', function () {
          toggleFields();
        })
      })
    }


    // Let's initialize these functions ... booom!!!
    initialize();

  });
})(jQuery);