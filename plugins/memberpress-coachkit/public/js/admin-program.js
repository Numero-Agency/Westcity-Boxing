(function ($) {
  $(document).ready(function () {

    /**
     * Add events and perform actions that need to run immediately
     */
    function initialize() {
      // Make metaboxes sortable
      if ($('.program-tab-content').length) {
        $('.program-tab-content').sortable({
          stop: function (event, ui) {
            // What happens after drag and drop event
            resortTitleIndexes(this)
            adjustProgramDueOptions(this)
          }
        });
      };

      // select2
      if (jQuery.fn.select2) {
        $('[data-s2]').select2();
      }

      // conditional fields
      initConditionalFields();
      replaceIntervalText();

      // Hook actions
      $('a.nav-tab').on('click', toggleProgramNavTabs);
      $(document.body).on('focus', '.datepicker', initDatepicker);

      $(document).on('mouseenter mouseleave', '.mpch-metabox-white__header, .mpch-metabox__header', ToggleHoverElement)

      // Milestone
      $(document).on('click', '[data-action="add-milestone"]', addNewMilestone);
      $(document).on('click', '[data-action="remove-milestone"]', removeMilestone);
      $(document).on('click', '[data-action="toggle-milestone"], [data-action="collapse-group"]', toggleMetabox);
      $(document).on('click', '[data-action="collapse-course"], [data-action="collapse-download"], [data-action="collapse-checkin"]', toggleInnerMetaboxes);
      $(document).on('click', '[data-action="toggle-course"], [data-action="toggle-download"]', openMilestoneModals);
      $(document).on('click', '[data-action="highlight-course"], [data-action="highlight-download"]', highlightCourse);
      $(document).on('click', '[data-action="add-course"]', addCourseToMilestone);
      $(document).on('click', '[data-action="remove-milestone-course"]', removeCourseFromMilestone);
      $(document).on('click', '[data-action="add-download"]', addDownloadToMilestone);
      $(document).on('click', '[data-action="remove-download"]', removeSingleDownload);
      $(document).on('click', '[data-action="add-habit-checkin"]', addCheckinToHabit);
      $(document).on('click', '[data-action="add-milestone-checkin"]', addCheckinToMilestone);
      $(document).on('click', '[data-action="remove-checkin"]', removeCheckin);
      $(document).on('input', '[data-action="filter-downloads"]', filterDownloads)

      // Habit
      $(document).on('click', '[data-action="add-habit"]', addNewHabit);
      $(document).on('click', '[data-action="remove-habit"]', removeHabit);
      $(document).on('change', '.mpch-habit-interval', replaceIntervalText);

      // Group
      $(document).on('click', '[data-action="toggle-group"]', newGroupModal);
      $(document).on('click', '[data-action="edit-group"]', editGroupModal);
      $(document).on('click', '[data-action="remove-group"]', removeGroup);
      $(document).on('click', '[data-action="save-group"]', saveOrUpdateGroup);

      // Pool
      $(document).on('click', '[data-action="add-membership-program"]', addProgramToMembership);
      $(document).on('click', '[data-action="remove-membership-program"]', removeProgramToMembership);

      $('body').on('click', '.mpch-tooltip', meprTooltip);
    }

    /**
     * Toogles program nav tabs
     * @returns bool
     */
    function toggleProgramNavTabs() {
      // refreshMilestone('uu6353', 4);

      if ($(this).hasClass('nav-tab-active'))
        return false;

      var chosen = $(this).attr('id');
      $('a.nav-tab').removeClass('nav-tab-active');
      $(this).addClass('nav-tab-active');

      $('div.program-tab-content').hide();
      $('div.program-' + chosen).show();

      return false;
    }

    /**
     * Initialize all datepickers
     */
    function initDatepicker() {
      $(this).datepicker({
        altFormat: "yy-mm-dd",
        altField: $(this).next('.datepicker-alt'),
      });
    }

    function ToggleHoverElement() {
      $(this).find('[data-toggle').toggle()
    }

    /**
     * Toggles the metabox content, accordion style
     */
    function toggleMetabox() {
      // , .mpch-metabox__footer
      $(this).closest('.mpch-metabox').find('.mpch-metabox__content').toggle();
    }

    /**
     * Toggles the metabox content, accordion style
     */
    function toggleInnerMetaboxes() {
      console.log($(this).closest('.mpch-metabox-white'));
      $(this).closest('.mpch-metabox-white').find('.mpch-metabox__content').toggle();
    }

    /**
     * Adds a new milestone on button click
     * @returns void | false
     */
    function addNewMilestone() {
      // Exit if we have a max limit reached
      // if ($('.program-milestones .mpch-metabox').length >= ProgramCPT.milestone.max) return false;

      var row = ProgramCPT.milestone.blank.row;
      row = uuidFy(row);
      row = indexiFy(row, '.program-milestones .mpch-metabox');

      if ($(this).closest('.mpch-metabox').length > 0) {
        $(this).closest('.mpch-metabox').after(row);
      } else {
        $('.program-milestones .mpch-metabox-empty').after(row)
      }

      resortTitleIndexes($(this).closest('.program-tab-content').get(0));
      adjustProgramDueOptions($(this).closest('.program-tab-content').get(0));

      /**
       * Fires when a milestone has been added.
       *
       * @event postboxes#postbox-toggled
       * @type {Object}
       */
      $(document).trigger('milestone-added');
    }

    function openMilestoneModals() {
      openModal($(this), {}, {
        open: function () {
          var $target = $(this.st.el.attr('data-mfp-src'));
          var $milestone = $(this.st.el).closest('.mpch-metabox');

          courseModalData($milestone, $target);
          // close popup when footer "close button" is clicked
          $target.find('[data-dismiss="modal"]')
          $target.find('[data-dismiss="modal"]').on('click', function (event) {
            $.magnificPopup.close();
          });

          $target.on('close-modal', function () {
            $.magnificPopup.close();
          });

        },
        close: function () {
          var $milestone = $(this.st.el).closest('.mpch-metabox');
          var $target = $(this.st.el.attr('data-mfp-src'));
          // console.log($target);
          $target.find('[data-action="highlight-course"], [data-action="highlight-download"]').each(function () {
            // remove highlighted called from the list item, the light blue bg
            $(this).closest('li').removeClass('--highlighted')
            // uncheck the checkbox
            $(this).removeAttr('checked');
          })
        }
      });
    }


    /**
     * Handles adding a course to a milestone
     * @returns bool
     */
    function addCourseToMilestone() {
      var $modal = $(this).closest('.mpch-modal');

      // lets get the input elements checked
      var checkedInputs = $($modal.find('input:checked'));
      var uuid = $modal.data('ref-milestone-id');
      var $milestone = $('[data-uuid="' + uuid + '"]');
      var $courseMetabox = $milestone.find('div.mpch-metabox-course');

      // no checkbox checked, close modal
      if (checkedInputs.length === 0) {
        $modal.trigger('close-modal');
        return false;
      }

      // add list item from html template
      var $listTemplate = $courseMetabox.find('.mpch-list-group template').html();
      checkedInputs.each(function (i, input) {

        // if list already exists, dont add
        if ($courseMetabox.find(".mpch-list-group > .mpch-list-group__item input").filter(function () { return input.value == $(this).val(); }).length === 0) {
          $content = $listTemplate
            .replace('{course_title}', $(input).data('course-title'))
            .replace('{course_id}', $(input).data('course-id'))

          $courseMetabox.find('.mpch-list-group').append($content);
        };
      })

      // close the modal
      $modal.trigger('close-modal');

      // show the milestone footer section and white course metabox
      $milestone.find('.mpch-metabox__footer').show();
      $milestone.find('.mpch-metabox-course').show();
    }

    /**
     * Handles removing a course from a milestone
     */
    function removeCourseFromMilestone() {
      var $metabox = $(this).closest('.mpch-metabox-course');
      $(this).closest('li').remove();

      // Maybe hide course metabox
      if ($metabox.find('.mpch-list-group > .mpch-list-group__item').length == 0) {
        $metabox.hide();
      }

      hideEmptyMetaboxFooter();
    }

    /**
     * Handles removing a course from a milestone
     */
    function removeSingleDownload() {
      var id = $(this).data('id');
      var $metabox = $(this).closest('.mpch-metabox-download');
      $(this).closest('li').remove();


      // uncheck modal download input
      var $modal_input = $('div#download-popup').find('input[data-download-id="' + id + '"]');
      $modal_input.prop("checked", false);
      $modal_input.closest('li').removeClass('--highlighted');

      // uncheck the checkbox
      $(this).removeAttr('checked');

      // Maybe hide course metabox
      if ($metabox.find('.mpch-list-group > .mpch-list-group__item').length == 0) {
        $metabox.hide();
      }

      hideEmptyMetaboxFooter();
    }

    /**
     * Handles showing/hiding milestone footer DIV
     */
    function hideEmptyMetaboxFooter() {
      $('div.program-milestones .mpch-metabox, div.program-habits .mpch-metabox').each(function (index, metabox) {

        var content = $(metabox).find("div.mpch-metabox__footer > div").filter(function () {
          return $(this).css('display') !== 'none';
        });

        if (content.length === 0) {
          $(metabox).find('div.mpch-metabox__footer').hide();
        }
      })
    }

    function courseModalData($milestone, $target) {

      // copy milestone id on metabox to the modal. Only one element should have data-uuid, hence the ref
      $target.data('ref-milestone-id', $milestone.attr('data-uuid'));

    }



    /**
     * Replaces milestone with freshly gotten content from the server
     * @param {string} uuid uuid
     * @param {int} position index of milestone
     */
    function refreshMilestone(uuid, position) {
      var requestData = {
        security: ProgramCPT.nonce,
        milestone_position: position,
        milestone_uuid: uuid,
        post_id: $('input#post_ID').val()
      };

      // send AJAX request
      var request = wp.ajax.post('refresh_milestone', requestData);

      // handle success
      request.done(function ($res) {
        $('[data-uuid="' + $res.uuid + '"]').replaceWith($res.html)
        $(document).trigger('milestone-refreshed');
      });
    }

    /**
     * Remove a milestone on button click
     */
    function removeMilestone() {
      // See, it's easier to destroy than to build
      // if ($('.mpch-metabox').length == 1) return false;
      var metaboxes = $(this).closest('div.program-tab-content').get(0);
      $(this).closest('.mpch-metabox').remove();

      resortTitleIndexes(metaboxes);
      $(document).trigger('milestone-removed');
    }

    function addCheckinToHabit() {
      var row = ProgramCPT.habit.checkin;
      var $habit = $(this).closest('div.mpch-metabox');

      if ($(this).is(':checked')) {
        if ($habit.find('.mpch-metabox-checkin').length == 0) {
          var index = $('.program-habits .mpch-metabox').index($habit);
          var row = row.replaceAll('{index}', index + 1)
          $habit.find('.mpch-metabox__footer').append(row).show();
        }
        $habit.find('.mpch-metabox-checkin').show();
        $(document).trigger('close-tooltip');
        $habit.find('.mpch-metabox__footer').show();
      }
      else {
        $habit.find('.mpch-metabox-checkin').hide();
        hideEmptyMetaboxFooter();
      }
    }

    function addCheckinToMilestone() {
      var row = ProgramCPT.milestone.checkin;
      var $milestone = $(this).closest('div.mpch-metabox');

      if ($(this).is(':checked')) {
        if ($milestone.find('.mpch-metabox-checkin').length == 0) {
          var index = $('.program-milestones .mpch-metabox').index($milestone);
          var row = row.replaceAll('{index}', index + 1)
          $milestone.find('.mpch-metabox__footer').append(row).show();
        }

        $milestone.find('.mpch-metabox-checkin').show();
        $(document).trigger('close-tooltip');
        $milestone.find('.mpch-metabox__footer').show();
      }
      else {
        $milestone.find('.mpch-metabox-checkin').hide();
        hideEmptyMetaboxFooter();
      }
    }

    function removeCheckin(e) {
      e.preventDefault();
      $(this).closest('.mpch-metabox-checkin').hide();
      hideEmptyMetaboxFooter();
    }

    /**
     * Filter coaches by user inputted value
     *
     * @param {Object} e
     * @returns
     */
    function filterDownloads(e) {
      var value = e.target.value.trim();
      var $this = $(this);

      var requestData = {
        security: ProgramCPT.group.nonce,
        search: value,
        post_id: $('input#post_ID').val()
      };

      // send AJAX request
      var request = wp.ajax.post('filter_downloads', requestData);

      // handle success
      request.done(function (res) {
        console.log('res :>> ', res);
        $this.closest('.mpch-modal__body').find('.mpch-card').html(res);
      });
    }


    /**
     * Adds a new milestone on button click
     * @returns void | false
     */
    function addNewHabit() {
      // Exit if we have a max limit reached

      var row = ProgramCPT.habit.blank.row;
      row = uuidFy(row);
      row = indexiFy(row, '.program-habits .mpch-metabox');

      if ($('.program-habits .mpch-metabox').length >= 1) {
        $(this).before(row);
      } else {
        $('.program-habits .mpch-metabox-empty').after(row)
      }

      /**
       * Fires when a habit has been added to DOM.
       *
       * @event postboxes#postbox-toggled
       * @type {Object}
       */
      $(document).trigger('habit-added');
    }

    /**
     * Remove a milestone on button click
     */
    function removeHabit() {
      // if ($('.mpch-metabox').length == 1) return false;
      var metaboxes = $(this).closest('div.program-tab-content').get(0);
      $(this).closest('.mpch-metabox').remove();

      resortTitleIndexes(metaboxes);
      $(document).trigger('habit-removed');
    }

    /**
     * Relace interval text
     */
    function replaceIntervalText() {
      $element = $(this);

      if (this == window) {
        $element = $(document).find('.mpch-habit-interval');
      }

      $element.each(function () {
        var text = $(this).find("option:selected").data('unit');
        $(this).closest('.mpch-metabox__row').find('.mpch-habit-interval-unit').html(text)
      })
    }

    /**
     * Send request to create or update a group
     */
    function saveOrUpdateGroup() {
      var $modal = $(this).closest('.mpch-modal');

      // add loading class
      $modal.addClass('--loading');
      // $modal.data('group-id', $(this).data('edit-group'));

      // lets get the input elements inside the modal
      var form = $($modal.find('input, select, textarea')).serializeArray();

      // send AJAX request
      var request = wp.ajax.post('update_group', { form, security: ProgramCPT.group.nonce, group_id: $(this).data('group-id'), program_id: $('input#post_ID').val() });

      // handle success
      request.done(function (res) {
        $('#mpch-group-metabox').find('.mpch-metabox-rows').html(res)
        $.magnificPopup.close();
      });

      // handle failure
      request.fail(function (res) {
        $modal.find('.mpch-notice').html(`<p>${res}</p>`).show();
      });

      // always run
      request.always(function () { $modal.removeClass('--loading') });
    }

    function addProgramToMembership() {
      var $metabox = $(this).closest('.mpch-metabox');;
      var listTemplate = $metabox.find('template').html();
      var index = $(this).closest('.mpch-metabox').find('.mpch-metabox__content > .mpch-program-pool__row').length;

      var content = listTemplate.replaceAll('{index}', index);
      $(this).closest('.mpch-metabox__row').before(content)

      $('[data-s2]').select2();
    }

    function removeProgramToMembership() {
      $(this).closest('.mpch-metabox__row').remove();
    }

    /**
     * Send request to remove a group
     */
    function removeGroup() {
      var $element = $(this);
      var $modal = $(this).closest('.mpch-modal');

      // add loading class
      $modal.addClass('--loading');

      // send AJAX request
      var request = wp.ajax.post('remove_group', {
        security: ProgramCPT.group.nonce,
        group_id: $(this).data('group-id'),
        program_id: $('input#post_ID').val()
      });

      // handle success
      request.done(function (res) {
        $('#mpch-group-metabox').find('.mpch-metabox-rows').html(res)
        $.magnificPopup.close();
      });

      // handle failure
      request.fail(function (res) {
        res = $.isPlainObject(res) ? res.responseText : res;
        $modal.find('.mpch-notice').html(`<p>${res}</p>`).show();
      });

      // always run
      request.always(function () { $modal.removeClass('--loading') });
    }


    /**
     * Sends ajax request and opens modal for a new group
     */
    function newGroupModal() {
      var $element = $(this);

      var data = {
        action: 'fetch_group',
        post_id: $('input#post_ID').val(),
        security: ProgramCPT.group.nonce
      }

      var callbacks = {
        parseAjax: function (mfpResponse) {
          mfpResponse.data = $.parseJSON(mfpResponse.data);
        },
        ajaxContentAdded: function () {
          this.content.removeClass('mfp-hide')
          initConditionalFields()
        }
      }

      openModal($element, data, callbacks);
    }


    /**
     * Sends ajax request and opens modal for a known group
     */
    function editGroupModal() {
      var $element = $(this);
      var $modal = $(this).closest('.mpch-modal');

      // $modal.data('group-id', $(this).data('edit-group'));

      // add loading class
      $modal.addClass('--loading');

      var data = {
        action: 'fetch_group',
        security: ProgramCPT.group.nonce,
        group_id: $(this).data('group-id'),
        post_id: $('input#post_ID').val()
      }

      var callbacks = {
        parseAjax: function (mfpResponse) {
          mfpResponse.data = $.parseJSON(mfpResponse.data);
        },
        ajaxContentAdded: function () {
          this.content.removeClass('mfp-hide')
          $modal.removeClass('--loading')
          initConditionalFields()
          $(document).trigger('init-popper');
        }
      }

      openModal($element, data, callbacks);
    }

    /**
     * Re-sorts Milestone Indexes in the Title
     */
    function resortTitleIndexes(metaboxes) {
      $(metaboxes).find('.mpch-metabox').each(function (index) {
        $(this).find('.mpch-metabox__title span').html(index + 1)
      })
    }

    /**
     * Make sure only first milestone has "After Program Starts" option
     */
    function adjustProgramDueOptions(metaboxes) {
      // check if it's milestones, not habits
      if (!$(metaboxes).hasClass('program-milestones')) {
        return
      }

      $(metaboxes).find('.mpch-metabox').each(function (index, element) {
        var $firstOption = $(this).find('.mpch-metabox__start-goal option').first();

        if (0 === index) {
          $firstOption.show()
          $firstOption.prop("selected", true)
          $(this).find('.mpch-metabox__start-goal option').not(':first').hide()
        }
        else {
          $firstOption.hide()
          $firstOption.next().prop("selected", true)
          $(this).find('.mpch-metabox__start-goal option').not(':first').show()
        }
      })
    }

    /**
     * Add uuid to metabox input name
     * @param {string} metabox
     * @returns metabox|void
     */
    function uuidFy(metabox = '', uuid = null) {

      if (null === uuid) {
        uuid = uuidv4();
      }

      if (metabox) {
        metabox = metabox.replaceAll('{uuid}', uuid);
        return metabox;
      } else {
        $('mpch-metabox').each(function (metabox) {
          $(this).replaceAll('{uuid}', uuid);
        });
      }

    }

    /**
     * Add index to metabox when
     * @param {string} metabox
     * @returns metabox
     */
    function indexiFy(metabox = '', selector = '') {
      var count = $(selector).length;
      return metabox.replaceAll('{index}', count + 1);
    }

    /**
     * Generates uuid
     * @returns string
     */
    function uuidv4() {
      return ([1e7] + 1e3 + 4e3 + 8e3 + 1e11).replace(/[018]/g, c =>
        (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
      );
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

    /**
     * Higlights a course when checked
     */
    function highlightCourse(e) {
      if ($(this).is(':checked')) {
        $(this).closest('li').addClass('--highlighted')
      } else {
        $(this).closest('li').removeClass('--highlighted')
      }
    }

    /**
     * Handles adding downloads to a milestone
     * @returns bool
     */
    function addDownloadToMilestone() {
      var $modal = $(this).closest('.mpch-modal');

      // lets get the input elements checked
      var checkedInputs = $($modal.find('input:checked'));

      // get metabox
      var uuid = $modal.data('ref-milestone-id');
      var $milestone = $('[data-uuid="' + uuid + '"]');
      var $metabox = $milestone.find('div.mpch-metabox-download');

      // no checkbox checked, close modal
      if (checkedInputs.length === 0) {
        $modal.trigger('close-modal');
        return false;
      }

      // add list item from html template
      var $listTemplate = $metabox.find('.mpch-list-group template').html();
      checkedInputs.each(function (i, input) {
        // let's add if list does not already exist
        if ($metabox.find(".mpch-list-group > .mpch-list-group__item input").filter(function () { return input.value == $(this).val(); }).length === 0) {
          console.log('$(input) :>> ', $(input).prev().prop('outerHTML'));
          $content = $listTemplate
            .replace('{download_thumbnail}', $(input).prev().prop('outerHTML'))
            .replace('{download_title}', $(input).data('download-title'))
            .replaceAll('{download_id}', $(input).data('download-id'))

          $metabox.find('.mpch-list-group').append($content);
        };
      })

      // close the modal
      $modal.trigger('close-modal');

      // show the milestone footer section and white course metabox
      $milestone.find('.mpch-metabox__footer').show();
      $milestone.find('.mpch-metabox-download').show();
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


    function meprTooltip(e) {
      e.stopPropagation();
      var tooltip_title = $(this).find('.mepr-data-title').html();
      var tooltip_info = $(this).find('.mepr-data-info').html();
      $(this).pointer({
        'content': '<h3>' + tooltip_title + '</h3><p>' + tooltip_info + '</p>',
        'position': { 'edge': 'right', 'align': 'center' },
        //'buttons': function() {
        //  // intentionally left blank to eliminate 'dismiss' button
        //}
      })
        .pointer('open');
    }

    // Let's initialize these functions ... booom!!!
    initialize();

  });
})(jQuery);