<div x-data="{ open: false }" @courses-modal.window="open = $event.detail === <?php echo absint( $index ); ?>" @keydown.window.escape="open = false" x-init="" x-show="open" class="relative z-50" aria-labelledby="modal-title" x-ref="dialog" aria-modal="true">

  <div x-show="open" x-transition:enter="x-ease-out x-duration-300" x-transition:enter-start="x-opacity-0" x-transition:enter-end="x-opacity-100" x-transition:leave="x-ease-in x-duration-200" x-transition:leave-start="x-opacity-100" x-transition:leave-end="x-opacity-0" class="fixed  bg-gray-500 inset-0 bg-opacity-50 transition-opacity"></div>


  <div class="fixed inset-0 z-10 overflow-y-auto">
    <div class="flex min-h-full justify-center p-4 items-center sm:p-0">

      <div x-show="open" x-transition:enter="x-ease-out x-duration-300" x-transition:enter-start="x-opacity-0 x-translate-y-4 sm:x-translate-y-0 sm:x-scale-95" x-transition:enter-end="x-opacity-100 x-translate-y-0 sm:x-scale-100" x-transition:leave="x-ease-in x-duration-200" x-transition:leave-start="x-opacity-100 x-translate-y-0 sm:x-scale-100" x-transition:leave-end="x-opacity-0 x-translate-y-4 sm:x-translate-y-0 sm:x-scale-95"class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6" @click.away="open = false">
        <div>
          <h3 class="text-xl m-0"><?php esc_html_e( 'Courses', 'memberpress-coachkit' ); ?></h3>
          <div class="mt-3 sm:mt-5">
            <ul role="list" class="mt-4 divide-y divide-gray-200 border-b">
              <?php foreach ( $milestone->courses as $key => $course ) : ?>
                <li class="flex items-center justify-between space-x-3 py-4">
                  <div class="flex min-w-0 flex-1 items-center space-x-3">
                    <div class="flex-shrink-0">
                      <img class="h-10 w-10 rtl:ml-2 rounded-full" src="<?php echo esc_url_raw( get_the_post_thumbnail_url( $course->ID ) ); ?>" alt="">
                    </div>
                    <div class="min-w-0 flex-1 max-w-[65%]">
                      <p class="truncate text-sm font-medium text-gray-900 m-0"><?php echo esc_html( $course->title ); ?></p>

                      <div class="flex items-center space-x-4">
                        <div class="mpch-progress">
                          <div class="mpch-progress__bar --success" style="width: <?php echo esc_attr( $course->progress ); ?>%;"></div>
                        </div>
                        <span class="mpch-progress__percent"><?php printf( '%s%%', esc_attr( $course->progress ) ); ?></span>
                      </div>

                    </div>
                  </div>
                  <div class="flex-shrink-0">
                    <a href="<?php echo esc_url_raw( get_permalink( $course->ID ) ); ?>" target="_blank" class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                      <?php esc_html_e( 'View', 'memberpress-coachkit' ); ?>
                    </a>
                  </div>
                </li>
              <?php endforeach ?>
            </ul>



          </div>
        </div>
      </div>

    </div>
  </div>
</div>
