<div id="tab-milestones" class="-mb-10" x-description="'Customer Reviews' panel, show/hide based on tab state" x-data="tabPanel(0)" aria-labelledby="tab-reviews" x-init="init()" x-cloak x-show="selected" @tab-select.window="onTabSelect" role="tabpanel" tabindex="0">

  <div class="mt-8 flow-root">
    <div class="-mx-4 -my-2 overflow-auto sm:-mx-6 lg:-mx-8">
      <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8 px-4">
        <?php if ( $milestones ) : ?>
          <div class="overflow-hidden -mx-4 shadow ring-1  ring-black ring-opacity-5 sm:mx-0 sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-white">
                  <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left rtl:text-right text-sm font-semibold text-gray-900 sm:pl-6"><?php echo esc_html_e( 'Milestone', 'memberpress-coachkit' ); ?></th>
                    <th scope="col" class="hidden px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900 lg:table-cell"><?php echo esc_html_e( 'Goal', 'memberpress-coachkit' ); ?></th>
                    <th scope="col" class="px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900"><?php echo esc_html_e( 'Due', 'memberpress-coachkit' ); ?></th>
                    <th scope="col" class="hidden px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900 lg:table-cell"><?php echo esc_html_e( 'Courses', 'memberpress-coachkit' ); ?></th>
                    <?php if ( is_plugin_active( 'memberpress-downloads/main.php' ) ) : ?>
                    <th scope="col" class="hidden px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900 lg:table-cell"><?php echo esc_html_e( 'Downloads', 'memberpress-coachkit' ); ?></th>
                    <?php endif; ?>
                    <th scope="col" class="relative text-left rtl:text-right text-sm py-3.5 pl-3 pr-4 sm:pr-6"><?php echo esc_html_e( 'Complete', 'memberpress-coachkit' ); ?></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white" id="enrollment-milestones">
                  <?php
                  $modal_downloads_str = '';
                  $modal_courses_str = '';
                  foreach ( $milestones as $index => $milestone ) :
                    $data = [
                      'enrollment_id' => $enrollment_id,
                      'isChecked'    => strtolower( $milestone->status ) === 'complete',
                      'startsInFuture' => $starts_in_future,
                      'index'        => $index,
                      'milestone_id' => $milestone->id,
                    ];
                    ?>
                    <tr x-data="milestone(<?php echo esc_html( wp_json_encode( $data ) ); ?>)" @closing-tooltip.window="hideLoader">
                      <td class="relative py-4 pl-4 pr-3 text-sm sm:pl-6">

                        <div class="hidden lg:block font-medium text-gray-900"><?php echo esc_html( $index + 1 ); ?></div>
                        <div class="sm:block lg:hidden font-medium text-gray-900"><?php echo esc_html( $milestone->title ); ?></div>
                        <div class="mt-1 flex flex-col text-gray-500 sm:block lg:hidden">

                          <?php if ( $milestone->courses ) : ?>
                            <a @click.prevent="$dispatch('courses-modal', <?php echo esc_attr( $index ); ?>)" href="" class="text-darkblue-50 font-semibold">
                              <span><?php echo esc_html( count( $milestone->courses ) ); ?></span> <?php esc_html_e( 'courses', 'memberpress-coachkit' ); ?>
                            </a>
                          <?php endif; ?>
                          <?php if ( $milestone->courses && $milestone->downloads ) : ?>
                            <span class="hidden sm:inline">·</span>
                          <?php endif; ?>
                          <?php if ( $milestone->downloads ) : ?>
                            <a @click="$dispatch('downloads-modal', '<?php echo 'm-'.esc_attr( $index ); ?>')" href="javascript:void(0)" class="text-darkblue-50 font-semibold">
                              <span><?php echo esc_html( count( $milestone->downloads ) ); ?></span> <?php esc_html_e( 'downloads', 'memberpress-coachkit' ); ?>
                            </a>
                          <?php endif; ?>
                        </div>
                      </td>
                      <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell"><?php echo esc_html( $milestone->title ); ?></td>
                      <td class="px-3 py-3.5 text-sm text-gray-500"><?php echo esc_html( $milestone->due ); ?></td>
                      <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell">
                        <?php if ( $milestone->courses ) : ?>
                          <a @click.prevent="$dispatch('courses-modal', <?php echo esc_attr( $index ); ?>)" href="" class="text-darkblue-50 font-semibold no-underline">
                            <span><?php echo esc_html( count( $milestone->courses ) ); ?></span> <?php esc_html_e( 'available', 'memberpress-coachkit' ); ?>
                          </a>
                          <?php
                          $modal_courses_str .= mpch_get_string( 'theme/partials/modal-courses', compact( 'milestone', 'index' ) );
                          ?>
                        <?php else : ?>
                          &mdash;
                        <?php endif; ?>
                      </td>
                      <?php if ( is_plugin_active( 'memberpress-downloads/main.php' ) ) : ?>
                      <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell">
                        <?php if ( $milestone->downloads ) : ?>
                          <a @click="$dispatch('downloads-modal', '<?php echo 'm-'.esc_attr( $index ); ?>')" href="javascript:void(0)" class="text-darkblue-50 font-semibold no-underline">
                            <span><?php echo esc_html( count( $milestone->downloads ) ); ?></span> <?php esc_html_e( 'available', 'memberpress-coachkit' ); ?>
                          </a>
                          <?php
                          $modal_downloads_str .= mpch_get_string('theme/partials/modal-downloads', [
                            'task' => $milestone,
                            'id' => 'm-'.$index,
                          ]);
                          ?>
                        <?php else : ?>
                          &mdash;
                        <?php endif; ?>
                      </td>
                      <?php endif; ?>
                      <td class="py-3.5 pl-3 pr-4 text-sm font-medium sm:pr-6">
                        <div class="ml-3 flex h-6 items-center">

                          <div x-data="tooltip" x-init="initPopper('left')" class="bg-gradient-to-tr p-1 rounded-full">
                            <div x-ref="popperButton">
                              <svg x-cloak x-show="showLoader()" x-ref="popperSVG" class="animate-spin align-middle h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                              </svg>
                              <input type="checkbox" @click="handleClick" x-model="isChecked" x-show="!showLoader()" :disabled="startsInFuture" class="h-5 w-5 rounded border-gray-300 cursor-pointer text-green-600 focus:ring-green-600 disabled:opacity-50 tw-checkbox">
                            </div>

                            <div x-ref="popperElement" class="hidden">
                              <p x-show="!isChecked" class="font-bold pb-2 m-0"><?php esc_html_e( 'Are you sure?', 'memberpress-coachkit' ); ?></p>
                              <p x-show="isChecked"><?php esc_html_e( 'Milestone cannot be unchecked', 'memberpress-coachkit' ); ?></p>
                              <p x-show="!isChecked" class="pb-2 m-0"><?php esc_html_e( 'Marking a milestone complete cannot be undone', 'memberpress-coachkit' ); ?></p>
                              <div x-show="!isChecked" class="flex justify-between mt-4">
                                <div class="flex items-center"></div>
                                <div class="flex items-center">
                                  <button @click="hideTooltip, hideLoader()" class="focus:outline-none focus:text-gray-400 mr-2 cursor-pointer"><?php esc_html_e( 'Cancel', 'memberpress-coachkit' ); ?></button>
                                  <button @click="hideTooltip, markComplete()" class="focus:outline-none bg-darkblue-50 transition duration-150 ease-in-out hover:bg-darkblue rounded text-white px-5 py-1 "><?php esc_html_e( 'Yes', 'memberpress-coachkit' ); ?></button>
                                </div>
                              </div>
                            </div>
                          </div>

                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
          </div>
        <?php echo $modal_downloads_str; ?>
        <?php echo $modal_courses_str; ?>
        <?php endif; ?>

        <?php if ( empty( $milestones ) ) : ?>
          <?php printf( '<p class="italic">%s</p>', esc_html__( "This program doesn't include any milestones.", 'memberpress-coachkit' ) ); ?>
        <?php endif; ?>
      </div>


    </div>
  </div>
</div>
