(function ($) {
  $(document).ready(function () {

    /**
     * Add events and perform actions that need to run immediately
     */
    function initialize() {

      $(".mpch-select2-data").select2({
        ajax: {
          url: ajaxurl,
          dataType: 'json',
          type: "POST",
          delay: 250,
          data: function (params) {
            return {
              security: ActivityUSER.nonce,
              q: params.term, // search query
              action: 'filter_students' // AJAX action for admin-ajax.php
            };
          },
          processResults: function (data, params) {
            return {
              results: data.data,
            };
          },
          cache: false
        },
        // placeholder: 'Search for a repository',
        minimumInputLength: 1,
      });


      $(".mpch-select2-data").on("select2:select", function (e) {
        // Do something
        var data = e.params.data;
        location.href = data.url;
        // console.log(data);
      });


      $("#activity-load-more-button").on('click', function (e) {
        e.preventDefault();

        var $button = $(this);
        var page = $button.data('page')
        // console.log(page);
        $.ajax({
          url: ajaxurl,
          type: 'post',
          data: {
            action: 'load_more_activities',
            page: ++page,
            security: ActivityUSER.nonce,
            activity: $('#activity-filter').val(),
          },
          success: function (response) {
            // console.log($button.data('page'));
            if (response.data.max_pages === page) {
              $button.hide()
            }
            $('.mpch-recent-activities__rows').append(response.data.activities.map(function (str) {
              return '<div class="mpch-recent-activities__entry space-x-2"><p>' + str.message + '</p><p class="mpch-recent-activities__time">' + str.date + '</p></div>';
            }))

            $button.attr('data-page', page).data('page', page);
          }
        });
        // }
      });


      $("#activity-filter").on('change', function (e) {
        var page = $('#activity-load-more-button').data('page');
        e.preventDefault();
        var page = 1;
        $.ajax({
          url: ajaxurl,
          type: 'post',
          data: {
            action: 'load_filtered_activities',
            page: page,
            activity: $(this).val(),
            security: ActivityUSER.nonce,
          },
          success: function (response) {
            if (response.data.max_pages === page || 1 >= response.data.max_pages) {
              $('#activity-load-more-button').hide()
            } else {
              $('#activity-load-more-button').show()
            }

            $('.mpch-recent-activities__rows').html(response.data.activities.map(function (str) {
              // return '<div class="mpch-recent-activities__entry">' + str + '</div>';
              return '<div class="mpch-recent-activities__entry space-x-2"><p>' + str.message + '</p><p class="mpch-recent-activities__time">' + str.date + '</p></div>';
            }))
            // page++;
          }
        });
        // }
      });

    }

    // Let's initialize these functions ... booom!!!
    initialize();

  });
})(jQuery);