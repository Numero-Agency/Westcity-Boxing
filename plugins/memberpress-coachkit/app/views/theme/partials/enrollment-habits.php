<div id="tab-habits" x-data="tabPanel(0)" aria-labelledby="tab-reviews" @tab-select.window="onTabSelect" x-init="init()" x-cloak x-show="selected" role="tabpanel" tabindex="1">
  <div class="mt-8 flow-root">
    <div class="-mx-4 -my-2 overflow-auto sm:-mx-6 lg:-mx-8">

      <div class="px-4 sm:px-6 lg:px-8 space-y-6 mb-6">
        <?php foreach ( $habits as $index => $habit ) : ?>
          <div class="-mx-4 mt-10 ring-1 ring-gray-300 sm:mx-0 sm:rounded-lg overflow-hidden">

            <table class="min-w-full w-full divide-y divide-gray-300 table-fixed m-0">
              <thead class="bg-white">
                <tr>
                  <th scope="col" class="py-3.5 pl-4 pr-3 text-left rtl:text-right text-sm font-semibold text-gray-900 sm:pl-6"><?php echo esc_html_e( 'Goal', 'memberpress-coachkit' ); ?></th>
                  <?php if ( is_plugin_active( 'memberpress-downloads/main.php' ) )  : ?>
                  <th scope="col" class="py-3.5 pl-3 pr-4 lg:pr-16 sm:pr-6 text-sm font-semibold text-gray-900 text-right"><?php echo esc_html_e( 'Downloads', 'memberpress-coachkit' ); ?></th>
                  <?php endif; ?>
                </tr>
              </thead>
              <tbody x-data="habit(<?php echo esc_html( wp_json_encode( $habit ) ); ?>, <?php echo absint( $enrollment_id ); ?>)" @closing-tooltip.window="hideLoaders" class="divide-y divide-gray-200 bg-white" id="enrollment-habits">
                <tr>
                  <td class="relative py-4 pl-4 pr-3 text-sm sm:pl-6">
                    <div class="text-gray-900 font-normal"><?php echo esc_html( $habit->title ); ?></div>
                  </td>
                  <?php if ( is_plugin_active( 'memberpress-downloads/main.php' ) )  : ?>
                    <td class="relative py-3.5 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 lg:pr-16">
                      <?php if ( $habit->downloads ) : ?>
                        <a @click="$dispatch('downloads-modal', '<?php echo 'h-'.esc_attr( $index ); ?>')" href="javascript:void(0)" class="text-darkblue-50 font-semibold no-underline">
                          <span><?php echo esc_html( count( $habit->downloads ) ); ?></span> <?php esc_html_e( 'available', 'memberpress-coachkit' ); ?>
                        </a>
                        <?php
                        mpch_load_template('theme/partials/modal-downloads', [
                          'task'  => $habit,
                          'id' => 'h-'.$index,
                        ]);
                        ?>
                      <?php endif; ?>
                    </td>
                  <?php endif; ?>

                </tr>
                <tr>
                  <td colspan="2" class="p-0">
                    <section x-data="scrollCards" class="flex justify-center items-center">
                      <div class="flex-none">
                        <button @click="scrollLeft" class="py-4 px-3 text-gray-400 hover:text-gray-600 focus:text-gray-600">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-8 h-8">
                            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                          </svg>
                        </button>
                      </div>
                      <ul class="w-full whitespace-nowrap text-center items-start space-x-3 px-8 overflow-scroll overflow-no-scrollbar border-l border-r" x-ref="scrollContainer" class="overflow-scroll border-l border-r  flex flex-auto">

                        <template x-for="(date, index) in habit.due_dates">
                          <li class="flex-none flex-col items-center pt-3 inline-block" :class="{ 'bg-arrow': date.today }"  :title="formatDateFull(date)">
                            <div x-data="tooltip" x-init="initPopper()" class="bg-gradient-to-tr p-1 rounded-full">
                              <div x-ref="popperButton">
                                <svg x-cloak x-show="showLoader(date)" x-ref="popperSVG" class="animate-spin align-middle w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <input type="checkbox" @click="handleClick(date, $event)" x-model="date.isChecked" x-show="!showLoader(date)" :disabled="isInFuture(date)" class="h-5 w-5 rounded border-gray-300 cursor-pointer text-green-600 focus:ring-green-600 disabled:opacity-50 tw-checkbox" :class="{ 'bg-cancel': date.grafted }">
                              </div>

                              <div x-ref="popperElement" class="hidden">
                                <p x-show="!date.isChecked && !date.grafted" class="font-bold pb-2 m-0"><?php esc_html_e( 'Are you sure?', 'memberpress-coachkit' ); ?></p>
                                <p x-show="date.isChecked"><?php esc_html_e( 'Habit cannot be unchecked', 'memberpress-coachkit' ); ?></p>
                                <p x-show="date.grafted"><?php esc_html_e( 'No habit due for completion today', 'memberpress-coachkit' ); ?></p>
                                <p x-show="!date.isChecked && !date.grafted" class="pb-2 m-0">
                                  <span><?php esc_html_e( 'Marking a habit complete for', 'memberpress-coachkit' ); ?></span>
                                  <span x-text="formatDateFull(date)" class="underline underline-offset-4 decoration-dashed"></span>
                                  <span><?php esc_html_e( 'cannot be undone', 'memberpress-coachkit' ); ?></span>
                                </p>
                                <div x-show="!date.isChecked && !date.grafted" class="flex justify-between mt-4">
                                  <div class="flex items-center">
                                    <button @click="hideTooltip, hideLoader(date)" class="focus:outline-none focus:text-gray-400 mr-2 cursor-pointer"><?php esc_html_e( 'Cancel', 'memberpress-coachkit' ); ?></button>
                                    <button @click="hideTooltip, markComplete(date)" class="focus:outline-none bg-darkblue-50 transition duration-150 ease-in-out hover:bg-darkblue rounded text-white px-5 py-1 "><?php esc_html_e( 'Yes', 'memberpress-coachkit' ); ?></button>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <span :class="{'text-gray-500':isInFuture(date)}" class="text-xs font-semibold" x-text="formatDate(date)"></span>
                          </li>
                        </template>


                      </ul>
                      <div class="flex-none">
                        <button @click="scrollRight" class="py-4 px-2 text-gray-400 hover:text-gray-600 focus:text-gray-600">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-8 h-8">
                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                          </svg>
                        </button>
                      </div>
                    </section>
                  </td>
                </tr>

              </tbody>
            </table>
          </div>

        <?php endforeach; ?>

        <?php if ( empty( $habits ) ) : ?>
          <?php printf( '<p class="italic">%s</p>', esc_html__( "This program doesn't include any habits.", 'memberpress-coachkit' ) ); ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
