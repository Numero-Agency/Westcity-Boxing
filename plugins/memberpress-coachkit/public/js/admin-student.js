(function ($) {
  $(document).ready(function () {

    /**
     * Add events and perform actions that need to run immediately
     */
    function initialize() {
      $(document).on('click', '[data-trigger="add-student"], [data-trigger="new-student"]', openNewStudentModal)
      $(document).on('click', '[data-add="student"]', addStudent)
      $(document).on('click', '[data-new="student"]', newStudent)
      $(document).on('input', '[data-filter="student"]', filterPotentialStudents)
      $(document).on('input', '[data-filter="programs"]', filterPrograms)
      $(document).on('input', '[data-filter="programs"]', filterPrograms)
      $(document).on('click', '[data-trigger="enroll-student-modal"]', openStudentEnrollmentModal)
      $(document).on('click', '[data-enroll="student"]', enrollStudent)
      $(document).on('click', '[data-select-student]', selectStudentForEnrollment)
      $(document).on('click', '[data-trigger="add-new-note"]', addNewNote)
      $(document).on('click', '[data-trigger="edit-note"]', editNote)
      $(document).on('click', '[data-trigger="hide-note-editor"]', hideNoteEditor)
      $(document).on('click', '[data-trigger="create-note"]', createNote)
      $(document).on('click', '[data-trigger="update-note"]', updateNote)
      $(document).on('click', '[data-trigger="trash-note"]', trashNote)
      $(document).on('click', 'a.remove-enrollment-row', unEnrollStudent)
    }

    /**
     * Open coach modals
     * @param {Object} e
     */
    function openNewStudentModal(e) {
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
     * Filter coaches by user inputted value
     *
     * @param {Object} e
     * @returns
     */
    function filterPotentialStudents(e) {
      var value = e.target.value.trim();
      var $this = $(this);

      // if (value.length < 2 || value == '' && previousInput == '') return;

      var requestData = {
        security: StudentUSER.nonce,
        search: value,
        post_id: $('input#post_ID').val()
      };

      // send AJAX request
      var request = wp.ajax.post('filter_users', requestData);

      // handle success
      request.done(function (res) {
        $this.closest('.mpch-modal__body').find('.mpch-card').html(res.users);
        $this.closest('.mpch-modal__body').find('.mpch-pagination').html(res.count);
      });
    }

    /**
     * Filter coaches by user inputted value
     *
     * @param {Object} e
     * @returns
     */
    function filterPrograms(e) {
      var value = e.target.value.trim();
      var $this = $(this);
      var $modal = $(this).closest('.mpch-modal');

      if (value.length == 1) return;

      var requestData = {
        security: StudentUSER.nonce,
        search: value,
        student_id: $modal.data('student-id'),
      };

      // send AJAX request
      var request = wp.ajax.post('filter_programs', requestData);

      // handle success
      request.done(function (res) {
        $this.closest('.mpch-modal__body').find('.mpch-card').html(res.programs);
        $this.closest('.mpch-modal__body').find('.mpch-pagination').html(res.count);
      });
    }

    /**
     * Add new coach
     */
    function addStudent() {
      var $modal = $(this).closest('.mpch-modal');

      // add loading class
      $modal.addClass('--loading');
      $modal.find('.mpch-notice').hide();

      var form = $($modal.find('input')).serializeArray();

      var requestData = {
        security: StudentUSER.nonce,
        student_id: $('input[name="mpch-student-id[]"]:checked').val(),
        post_id: $('input#post_ID').val()
      };

      // send AJAX request
      var request = wp.ajax.post('add_student', requestData);

      request
        // handle success
        .done(function (res) {
          $modal.find('.mpch-notice__success').html(`<p>${res.message}</p>`).show();
        })

        // handle failure
        .fail(function (res) {
          res = $.isPlainObject(res) ? res.responseText : res;
          $modal.find('.mpch-notice__error').html(`<p>${res}</p>`).show();
        })

        // always run
        .always(function () { $modal.removeClass('--loading') });
    }

    /**
     * Add new coach
     */
    function newStudent() {
      var $modal = $(this).closest('.mpch-modal');

      // add loading class
      $modal.addClass('--loading');
      $modal.find('.mpch-notice').hide();

      var form = $($modal.find('input')).serializeArray();

      var requestData = {
        security: StudentUSER.nonce,
        form,
        post_id: $('input#post_ID').val()
      };

      // send AJAX request
      var request = wp.ajax.post('new_student', requestData);

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

    function openStudentEnrollmentModal(e) {
      e.preventDefault();

      openModal($(this), {}, {
        open: function () {
        },
        close: function () {
        }
      });
    }

    function enrollStudent() {
      var $modal = $(this).closest('.mpch-modal');

      // add loading class
      $modal.addClass('--loading');
      $modal.find('.mpch-notice').hide();

      var form = $($modal.find('input')).serializeArray();

      var requestData = {
        security: StudentUSER.nonce,
        group_id: $('input[name="mpch-student-group-id"]:checked').val(),
        student_id: $modal.data('student-id'),
      };

      // send AJAX request
      var request = wp.ajax.post('add_student_to_group', requestData);

      // handle success
      request.done(function (res) {
        $modal.find('.mpch-notice__success').html(`<p>${res}</p>`).show();
      });

      // handle failure
      request.fail(function (res) {
        res = $.isPlainObject(res) ? res.responseText : res;
        $modal.find('.mpch-notice__error').html(`<p>${res}</p>`).show();
      });

      // always run
      request.always(function () { $modal.removeClass('--loading') });

      // //BEGIN
      // $(".accordion__title").on("click", function(e) {
      //   // console.log('jerome')
      //   e.preventDefault();
      //   var $this = $(this);

      //   if (!$this.hasClass("accordion-active")) {
      //     $(".accordion__content").slideUp(400);
      //     $(".accordion__title").removeClass("accordion-active");
      //     $('.accordion__arrow').removeClass('accordion__rotate');
      //   }

      //   $this.toggleClass("accordion-active");
      //   $this.next().slideToggle();
      //   $('.accordion__arrow',this).toggleClass('accordion__rotate');
      // });
      // //END
    }

    function selectStudentForEnrollment() {
      if ($(this).attr('disabled')) {
        return;
      }
      $('.student-group-modal__list').removeClass('active')
      $(this).closest('.student-group-modal__list').addClass('active')
    }

    // $('.mpch-notes__editor').hide();
    function addNewNote() {
      // Hide the editor section initially

      // Hide the button
      $(this).parent().hide();
      // $('.mpch-notes__content').hide();

      // Show the editor section
      $('.mpch-notes__editor').show();
      $('.mpch-notes__editor .wp-editor-container').show();
      $('.mpch-notes__create-buttons').show();

      // Initialize the WordPress editor
      // wp.editor.initialize('editor-section', {
      //   'tinymce': true,
      //   'quicktags': true,
      //   mediaButtons: false
      // });
    }

    function editNote() {
      resetNoteEditor();

      var $note = $(this).closest('.mpch-notes__note');
      var editorId = $note.find('textarea').attr('id');

      $note.find('.wp-editor-container').show();

      wp.editor.initialize(editorId, {
        tinymce: false,
        quicktags: {
          buttons: "strong,em,link,block,del,ins,img,ul,ol,li,code,more,close"
        }
      });

      $note.find('.mpch-notes__static-note').hide();
      $note.find('.row-actions').hide();
      $('.mpch-notes__button-new').hide();

      $note.find('.mpch-notes__update-buttons').show();
    }

    function hideNoteEditor() {
      resetNoteEditor();

      // var $note = $(this).closest('.mpch-notes__note');

      // Hide the editor section initially
      // $('.mpch-notes__editor').hide();
      // $('.mpch-notes__create-buttons').hide();
      // $note.find('.wp-editor-container').hide();
      // $('.mpch-notes__update-buttons').hide();
      // $note.find('.mpch-notes__static-note').show();
      // $note.find('.row-actions').show();
      // $('.mpch-notes__button-new').show();
      // $('.mpch-notes__content').show();
    }

    function createNote() {
      var $this = $(this);
      var requestData = {
        security: StudentUSER.nonce,
        note: jQuery('textarea#add-note').val(),
        student_id: $(this).data("student-id")
      };

      // send AJAX request
      var request = wp.ajax.post('create_note', requestData);

      // handle success
      request.done(function (res) {
        var $firstRow = $this.closest('.mpch-coach-metabox__inside').find('tbody tr:first');
        var $trCount = $this.closest('.mpch-coach-metabox__inside').find('tr').length;

        if ($trCount == 1 && !$firstRow.hasClass('mpch-notes__note')) {
          $this.closest('.mpch-coach-metabox__inside').find('tr').replaceWith(res.note)
        } else {
          $firstRow.before(res.note)
        }

        resetNoteEditor()
        $this.closest('.mpch-coach-metabox__inside').find('.mpch-notes__editor .wp-editor-area').val('');

        var $container = $('.mpch-notes-metabox');
        if ($container.length) {
          $('html, body').animate({
            scrollTop: $container.offset().top
          }, 500); // You can adjust the duration as needed
        }

      });
    }

    function updateNote() {
      var $this = $(this);
      var requestData = {
        security: StudentUSER.nonce,
        note: $(this).closest('.mpch-notes__row-note').find('textarea').val(),
        student_id: $(this).data("student-id"),
        note_id: $(this).data("note-id")
      };

      // send AJAX request
      var request = wp.ajax.post('update_note', requestData);

      // handle success
      request.done(function (res) {
        $this.closest('tr').replaceWith(res.note);
        resetNoteEditor()
      });
    }

    function trashNote() {
      var $this = $(this);
      var requestData = {
        security: StudentUSER.nonce,
        note_id: $(this).data("note-id")
      };

      // send AJAX request
      var request = wp.ajax.post('trash_note', requestData);

      // handle success
      request.done(function () {
        $this.closest('tr').remove();
        resetNoteEditor()
      });
    }

    function resetNoteEditor() {
      $('.wp-editor-container').hide();
      $('.mpch-notes__update-buttons').hide();
      $('.mpch-notes__create-buttons').hide();
      $('.mpch-notes__static-note').show();
      $('.row-actions').show();
      $('.mpch-notes__button-new').show();
    }

    function unEnrollStudent(e) {
      e.preventDefault();
      if(confirm(StudentUSER.i10n.del_enrollment)) {
        var enrollment_id = jQuery(this).attr('data-value');
        var requestData = {
          security: StudentUSER.nonce,
          enrollment_id: enrollment_id
        };

        // send AJAX request
        var request = wp.ajax.post('unenroll_student', requestData);
        // handle success
        request.done(function (res) {
          $('tr#record_' + enrollment_id).fadeOut('slow');
        });
        request.fail(function (res) {
          alert(res)
        })
      }
    }

    // Let's initialize these functions ... booom!!!
    initialize();

  });
})(jQuery);