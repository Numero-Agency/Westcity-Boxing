(function ($) {
  $(document).ready(function () {

    /**
     * Add events and perform actions that need to run immediately
     */
    function initialize() {
      $(document).on('click', '[data-action="add-coach-modal"], [data-action="new-coach-modal"]', openNewCoachModal)
      $(document).on('click', '[data-action="assign-coach-modal"]', openAssignCoachModal)
      $(document).on('click', '[data-select-coach]', selectCoachForAssignment)
      $(document).on('click', '[data-action="assign-coach"]', assignCoach)
      $(document).on('click', '[data-action="add-coach"]', addCoach)
      $(document).on('click', '[data-action="new-coach"]', newCoach)
      $(document).on('input', '[data-action="filter-coach"]', filterCoaches)
    }

    /**
     * Open coach modals
     * @param {Object} e
     */
    function openNewCoachModal(e) {
      e.preventDefault();
      openModal($(this), {}, {
        open: function () {
        },
        close: function () {
        }
      });
    }

    function openAssignCoachModal(e) {
      e.preventDefault();

      openModal($(this), {}, {
        open: function () {
        },
        close: function () {
        }
      });
    }

    /**
     * Add maginificpopup to selectors'
     * Each selector HTML has a data-mfp-src data attribute
     */
    function openModal($element, data = {}, callbacks) {
      var options = {
        type: 'inline',
        midClick: true,
        closeMarkup: '<button title="%title%" type="button" class="mfp-close"><svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 1.41L12.59 0L7 5.59L1.41 0L0 1.41L5.59 7L0 12.59L1.41 14L7 8.41L12.59 14L14 12.59L8.41 7L14 1.41Z" fill="#3C434A"/></svg></button>',
        callbacks: $.extend({
          open: function () {
            // console.log('rad');

            // Allow submit when user presses "Enter"
            var target = this.st.el.attr('data-mfp-src');
            document.onkeydown = function () {
              var $modal = $(target);
              if (window.event.keyCode == '13') {
                $modal.find('[data-modal-submit]').click()
              }
            }

          }
        }, callbacks)
      };

      if (!$.isEmptyObject(data)) {
        $.extend(options, {
          type: 'ajax',
          dataType: 'json',
          ajax: {
            settings: {
              url: ajaxurl,
              data: data,
              type: "POST",
            }
          }
        })
      }

      $element.magnificPopup(options).magnificPopup('open');
    }

    function assignCoach() {
      var $modal = $(this).closest('.mpch-modal');
      var $checkedInput = $('input[name="mpch-coach-group-id"]:checked');
      var $list = $checkedInput.closest('li');

      // add loading class
      $modal.addClass('--loading');
      $modal.find('.mpch-notice').hide();

      var form = $($modal.find('input')).serializeArray();

      var requestData = {
        security: CoachUSER.nonce,
        group_id: $checkedInput.val(),
        coach_id: $modal.data('coach-id'),
      };

      // send AJAX request
      var request = wp.ajax.post('assign_coach_to_group', requestData);

      // handle success
      request.done(function (res) {
        $modal.find('.mpch-notice__success').html(`<p>${res}</p>`).show();
        $checkedInput.prop('checked', false);
        $list.addClass('custom-disabled-one').removeClass('active')
      });

      // handle failure
      request.fail(function (res) {
        res = $.isPlainObject(res) ? res.responseText : res;
        $modal.find('.mpch-notice__error').html(`<p>${res}</p>`).show();
      });

      // always run
      request.always(function () { $modal.removeClass('--loading') });
    }


    function selectCoachForAssignment() {
      if ($(this).attr('disabled')) {
        return;
      }
      $('.coach-group-modal__list').removeClass('active')
      $(this).closest('.coach-group-modal__list').addClass('active')
    }

    /**
     * Filter coaches by user inputted value
     *
     * @param {Object} e
     * @returns
     */
    function filterCoaches(e) {
      var value = e.target.value.trim();
      var $this = $(this);
      // if (value.length < 2 || value == '') return;

      var requestData = {
        security: CoachUSER.nonce,
        search: value,
        post_id: $('input#post_ID').val()
      };

      // send AJAX request
      var request = wp.ajax.post('filter_coaches', requestData);

      // handle success
      request.done(function (res) {
        $this.closest('.mpch-modal__body').find('.mpch-card').html(res);
      });
    }

    /**
     * Add new coach
     */
    function addCoach() {
      var $modal = $(this).closest('.mpch-modal');

      // add loading class
      $modal.addClass('--loading');
      $modal.find('.mpch-notice').hide();

      var form = $($modal.find('input')).serializeArray();

      var requestData = {
        security: CoachUSER.nonce,
        coach_id: $('input[name="mpch-coach-id[]"]:checked').val(),
        post_id: $('input#post_ID').val()
      };

      // send AJAX request
      var request = wp.ajax.post('add_coach', requestData);

      // handle success
      request.done(function (res) {
        // console.log(res);
        $modal.find('.mpch-notice__success').html(`<p>${res.message}</p>`).show();
      });

      // handle failure
      request.fail(function (res) {
        res = $.isPlainObject(res) ? res.responseText : res;
        $modal.find('.mpch-notice__error').html(`<p>${res}</p>`).show();
      });

      // always run
      request.always(function () { $modal.removeClass('--loading') });
    }

    /**
     * Add new coach
     */
    function newCoach() {
      var $modal = $(this).closest('.mpch-modal');

      // add loading class
      $modal.addClass('--loading');
      $modal.find('.mpch-notice').hide();

      var form = $($modal.find('input')).serializeArray();

      var requestData = {
        security: CoachUSER.nonce,
        form,
        post_id: $('input#post_ID').val()
      };

      // send AJAX request
      var request = wp.ajax.post('new_coach', requestData);

      // handle success
      request.done(function (res) {
        $modal.find('.mpch-notice__success').html(`<p>${res}</p>`).show();
        $modal.find('input').val('')
      });

      // handle failure
      request.fail(function (res) {
        res = $.isPlainObject(res) ? res.responseText : res;
        $modal.find('.mpch-notice__error').html(`<p>${res}</p>`).show();
      });

      // always run
      request.always(function () { $modal.removeClass('--loading') });
    }

    // Let's initialize these functions ... booom!!!
    initialize();

  });
})(jQuery);