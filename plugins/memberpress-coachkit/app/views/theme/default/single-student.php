<div x-data="{ open: false }" @keydown.window.escape="open = false" class="mpch-container alignwide">

  <?php if ( $view->is_readylaunch ) : ?>
  <div class="px-4 sm:px-6 lg:px-8">
  <?php else : ?>
  <div class="max-w-5xl mx-auto py-10">
  <?php endif; ?>

    <div class="lg:flex lg:items-center lg:justify-between mb-14" @download-file.window="$store.coaching.download($event.detail)">
      <div class="min-w-0 flex-1">
        <h1 class="text-2xl font-semibold leading-6 text-gray-900"><?php echo esc_html( $view->student->name ); ?></h1>
      </div>
    </div>

    <!-- Tab panels -->
    <div>
      <div class="mt-8 flow-root">
        <div class="-mx-4 -my-2 overflow-auto sm:-mx-6 lg:-mx-8 space-y-6">

          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8 px-4 space-y-4">
            <h2 class="mx-auto max-w-2xl text-base font-semibold leading-6 text-gray-900 lg:mx-0 lg:max-w-none">
              <?php echo esc_html_e( 'Enrollments', 'memberpress-coachkit' ); ?>
            </h2>

            <div class="overflow-hidden -mx-4 shadow ring-1  ring-black ring-opacity-5 sm:mx-0 sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-white">
                  <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left rtl:text-right text-sm font-semibold text-gray-900 sm:pl-6"><?php echo esc_html_e( 'Program', 'memberpress-coachkit' ); ?></th>
                    <th scope="col" class="hidden px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900 lg:table-cell"><?php echo esc_html_e( '', 'memberpress-coachkit' ); ?></th>
                    <th scope="col" class="hidden px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900 lg:table-cell"><?php echo esc_html_e( 'Started', 'memberpress-coachkit' ); ?></th>
                    <th scope="col" class="hidden px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900 lg:table-cell"><?php echo esc_html( $options->label_for_coach ); ?></th>
                    <th scope="col" class="relative text-left rtl:text-right text-sm py-3.5 pl-3 pr-4 sm:pr-6"><?php echo esc_html_e( 'Progress', 'memberpress-coachkit' ); ?></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white" id="enrollment-milestones">
                  <?php
                  foreach ( $view->student->enrollments as $index => $enrollment ) :
                    ?>
                    <tr>
                      <td class="relative py-4 pl-4 pr-3 text-sm sm:pl-6">
                        <div class="hidden lg:block font-medium text-gray-900"><?php echo esc_html( $enrollment->title ); ?></div>
                        <div class="sm:block lg:hidden font-medium text-gray-900"><?php echo esc_html( $enrollment->title ); ?></div>
                      </td>
                      <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell"><?php echo $enrollment->off_track ? '<span class="bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">' . esc_html__( 'Off Track', 'memberpress-coachkit' ) . '</span>' : ''; ?></td>
                      <td class="px-3 py-3.5 text-sm text-gray-500"><?php echo esc_html( $enrollment->started ); ?></td>
                      <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell">
                        <?php echo esc_html( $enrollment->coach ); ?>
                      </td>
                      <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell">
                        <div class="flex items-center space-x-1">
                          <div class="mpch-progress">
                            <div class="mpch-progress__bar --success" style="width: <?php echo esc_html( $enrollment->progress ); ?>%;"></div>
                          </div>
                          <span class="mpch-progress__percent"><?php echo esc_html( $enrollment->progress ); ?>%</span>
                        </div>
                        <?php // echo esc_html( $enrollment->progress );
                        ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8 px-4 space-y-4">
            <h2 class="mx-auto max-w-2xl text-base font-semibold leading-6 text-gray-900 lg:mx-0 lg:max-w-none">
              <?php echo esc_html_e( 'Active Memberships', 'memberpress-coachkit' ); ?>
            </h2>

            <div class="overflow-hidden -mx-4 shadow ring-1  ring-black ring-opacity-5 sm:mx-0 sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-white">
                  <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left rtl:text-right text-sm font-semibold text-gray-900 sm:pl-6"><?php echo esc_html_e( 'ID', 'memberpress-coachkit' ); ?></th>
                    <th scope="col" class="hidden px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900 lg:table-cell"><?php echo esc_html_e( 'Membership', 'memberpress-coachkit' ); ?></th>
                    <th scope="col" class="hidden px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900 lg:table-cell"><?php echo esc_html_e( 'Term', 'memberpress-coachkit' ); ?></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white" id="enrollment-milestones">

                  <?php
                  if ( empty( $view->student->memberships ) ) {
                    printf( '<tr><td colspan="4" class="p-4">%s</td></tr>', esc_html__( 'No active memberships found.', 'memberpress-coachkit' ) );
                  }

                  foreach ( $view->student->memberships as $index => $membership ) :
                    ?>
                    <tr>
                      <td class="relative py-4 pl-4 pr-3 text-sm sm:pl-6">
                        <div class="hidden lg:block font-medium text-gray-900"><?php echo esc_html( $membership->id ); ?></div>
                        <div class="sm:block lg:hidden font-medium text-gray-900"><?php echo esc_html( $membership->id ); ?></div>
                      </td>
                      <td class="hidden px-3 py-3.5 text-sm text-gray-500 lg:table-cell">
                        <a href="<?php echo esc_url( $membership->url ); ?>" class="text-darkblue-50"><?php echo esc_html( $membership->product ); ?></a>
                      </td>
                      <td class="px-3 py-3.5 text-sm text-gray-500">
                        <?php echo wp_kses_post( $membership->terms ); ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <section x-data="notes(<?php echo esc_html( wp_json_encode( $view->student_notes ) ); ?>)" class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8 px-4 space-y-4" aria-labelledby="notes-title">
            <h2 class="mx-auto max-w-2xl text-base font-semibold leading-6 text-gray-900 lg:mx-0 lg:max-w-none">
              <?php echo esc_html_e( 'Notes', 'memberpress-coachkit' ); ?>
            </h2>
            <div class="bg-white shadow sm:overflow-hidden sm:rounded-lg">
              <div class="divide-y divide-gray-200">
                <div class="px-4 py-6 sm:px-6">
                  <p x-show="emptyNotes" x-cloak><?php esc_html_e( 'No notes found.', 'memberpress-coachkit' ); ?></p>

                  <ul role="list" class="divide-y">
                    <template x-for="(note, index) in notes" hidden>
                      <li class="py-4">
                        <div class="flex space-x-3">
                          <div class="flex-shrink-0">
                            <img class="h-10 w-10 rtl:ml-2 rounded-full" :src="note.thumbnail_url" alt="">
                          </div>
                          <div>
                            <div class="text-sm">
                              <a href="#" class="font-medium text-gray-900" x-text="note.name"></a>
                            </div>
                            <div class="mt-1 text-sm text-gray-700" x-text="note.note"></div>
                            <div class="mt-2 space-x-2 text-xs">
                              <span class="font-medium text-gray-500" x-text="note.time"></span>
                              <!-- space -->
                              <span x-show="author(note)">
                                <span class="font-medium text-gray-500">·</span>
                                <button @click="editNote(note)" type="button" class="font-medium text-gray-900"><?php echo esc_html_e( 'Edit', 'memberpress-coachkit' ); ?></button>
                                <button @click="deleteNote(note)" type="button" class="font-medium text-gray-900"><?php echo esc_html_e( 'Delete', 'memberpress-coachkit' ); ?></button>
                              </span>
                            </div>
                          </div>
                        </div>
                      </li>
                    </template>
                  </ul>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-6 sm:px-6">
                <div class="flex space-x-3">
                  <div class="flex-shrink-0">
                    <img class="h-10 w-10 rtl:ml-2 rounded-full" src="<?php echo esc_url( get_avatar_url( get_current_user_id() ) ); ?>" alt="">
                  </div>
                  <div class="min-w-0 flex-1">
                    <form action="#">
                      <div>
                        <textarea x-model="form.note" x-ref="notetextbox" rows="3" class="block w-full rounded-md border-0 px-2 py-1.5 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-1 focus:ring-inset focus:ring-darkblue-50 sm:text-sm sm:leading-6 outline-none" placeholder="Add a note"></textarea>
                      </div>
                      <div class="mt-3 flex items-center justify-end">
                        <button @click="createNote" x-show="!selectedNote" type="button" class="mpch-button"><?php echo esc_html_e( 'Create Note', 'memberpress-coachkit' ); ?></button>
                        <div x-show="selectedNote" class="flex items-center space-x-2 text-sm font-semibold">
                          <button @click="cancelEditNote" type="button"><?php echo esc_html_e( 'Cancel', 'memberpress-coachkit' ); ?></button>
                          <button @click="updateNote" type="button" class="mpch-button"><?php echo esc_html_e( 'Update Note', 'memberpress-coachkit' ); ?></button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </section>


        </div>
      </div>

    </div>
  </div>


</div>
