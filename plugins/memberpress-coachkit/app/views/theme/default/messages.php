<?php

/**
 * The template for displaying all enrollments
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package coaching
 */

?>

<div x-data="{ open: false }" @keydown.window.escape="open = false" class="mpch-container alignwide">
  <?php if ( $view->is_readylaunch ) : ?>
    <div class="px-4 sm:px-6 lg:px-8">
      <?php else : ?>
    <div class="max-w-5xl mx-auto py-10">
    <?php endif; ?>

    <div x-data="messages(<?php echo esc_html( wp_json_encode( $view->messages ) ); ?>)" class="min-w-0 h-[36rem] flex-1 border overflow-hidden border-gray-200 md:flex bg-white rounded-lg">

      <!-- Message list-->
      <aside x-show="roomsNotEmpty && !emptyState" x-ref="sidebarRef" class="hidden md:flex lg:flex-shrink-0 sm:w-72" x-cloak>
        <div class="relative flex h-full w-full lg:w-96 flex-col border-r border-gray-200 overflow-hidden">
          <div id="start-new-message" class="flex justify-center py-2">

            <div x-data="tooltip" x-init="initPopper()" class="bg-gradient-to-tr p-1 rounded-full">
              <div x-ref="popperButton">
                <a href="#0" class="hover:text-darkblue-50 text-darkblue group flex gap-1 font-medium rounded-md p-2 text-sm leading-6">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                  </svg>
                  <?php echo esc_html_x( 'Start New Message', 'ui', 'memberpress-coachkit' ); ?>
                </a>
              </div>

              <?php
              mpch_load_template( 'theme/partials/new-chat-dropdown' );
              ?>
            </div>

          </div>
          <nav aria-label="Message list" class="min-h-0 flex-1 overflow-y-auto">
            <ul id="message-rooms" role="list" class="divide-y divide-gray-200">
              <template x-for="(room, index) in rooms" hidden>
                <li @click="selectThread(index)" class="mpch-chat-room relative bg-white px-6 py-5 cursor-pointer" :class="{ 'bg-blue-50 hover:bg-blue-50 ': thread.room_id === room.room_id, 'bg-white hover:bg-gray-50': thread.room_id !== room.room_id }" :id="`room-${room.room_id}`">
                  <div class="flex justify-between space-x-3">
                    <div class="min-w-0 flex-1">
                      <!-- <a href="#" class="block focus:outline-none"> -->
                      <span class="absolute inset-0" aria-hidden="true"></span>
                      <p class="truncate text-sm font-medium m-0" :class="{ 'text-darkblue': thread.room_id === room.room_id, 'text-gray-900': thread.room_id !== room.room_id }" x-text="room.recipient"></p>
                      <p class="truncate text-sm text-gray-500 m-0" x-text="latestRoomMessage(room)"></p>
                      <!-- </a> -->
                    </div>
                    <time datetime="2021-01-27T16:35" class="flex-shrink-0 whitespace-nowrap text-sm text-gray-500" x-text="displayRoomTime(room)"></time>
                  </div>
                </li>
              </template>
            </ul>
          </nav>
        </div>
      </aside>

      <!-- Chat section -->
      <section x-show="roomsNotEmpty" x-ref="messagesRef" aria-labelledby="message-heading" class="flex  min-w-0 flex-1  flex-col overflow-hidden" x-cloak>
        <div class=" flex-1">
          <div class="bg-white pb-3 pt-2.5 shadow">
            <div class="px-4 flex sm:items-center sm:justify-between sm:px-6 lg:px-8">
              <button @click="showChatSidebar()" type="button" class="block md:hidden text-darkblue-50">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                </svg>
              </button>

              <div class="sm:w-0 flex-1">
                <p id="message-heading" class="text-sm text-center text-gray-900 m-0" x-text="thread.recipient"></p>
              </div>
            </div>
          </div>
        </div>

        <div class=" overflow-y-scroll h-full overflow-no-scrollbar" x-ref="messageList">
          <p x-show="showLoadMoreButton" class="text-center py-4 text-sm">
            <button @click.prevent="loadMore" type="button" class="text-darkblue-50"><?php echo esc_html_x( 'Load More', 'ui', 'memberpress-coachkit' ); ?></button>
          </p>
          <ul role="list" class="space-y-2 py-4 px-4 sm:space-y-4 sm:px-6 lg:px-8">
            <template x-for="conversation in thread.conversations" hidden>
              <li class="chat-item flex flex-col max-w-[30rem] mt-1" :class="{ 'chat-right': authored(conversation), 'chat-left': !authored(conversation) }">
                <div class="chat-item__message relative space-y-4 flex-col ml-1 h-auto bg-blue-100 bg-opacity-80 p-2.5 font-normal rounded-md items-end text-sm">
                  <template x-if="conversation.message" hidden>
                    <div x-html="formatMessage(conversation.message)"></div>
                  </template>
                  <!-- Image Layout -->
                  <template x-if="conversation && onlyImages(conversation.attachments)" hidden>
                    <ul class="flex space-x-2">
                      <template x-for="attachment in conversation.attachments" hidden>
                        <li @click="openFileInNewTab(attachment.url)" class="cursor-pointer">
                          <img :src="attachment.url" alt="" class="w-24 h-full object-cover rounded">
                        </li>
                      </template>
                    </ul>
                  </template>

                  <!-- Files layout -->
                  <template x-if="conversation && !onlyImages(conversation.attachments)" hidden>
                    <ul class="grid">
                      <template x-for="attachment in conversation.attachments" hidden>
                        <li @click="openFileInNewTab(attachment.url)" class="flex items-center p-2 rounded cursor-pointer hover:bg-white/30">
                          <template x-if="isImage(attachment.type)">
                            <img class="h-4 w-4 mr-3" :src="attachment.url" alt="Image Thumbnail">
                          </template>
                          <template x-if="!isImage(attachment.type)">
                            <img class="h-4 w-4 mr-3" :src="getFileIcon(attachment.type)" alt="PDF Icon">
                          </template>
                          <div class="leading-none text-xs truncate overflow-hidden" x-text="attachment.name"></div>
                        </li>
                      </template>
                    </ul>
                  </template>

                </div>
                <div class="space-x-2 flex items-center mt-1 divide-x" style="font-size: 11px;">
                  <span class="text-gray-500 pl-1"  x-text="conversation.sender"></span>
                  <span x-show="fromCoach(conversation)" class="inline-flex items-center rounded-md bg-green-50 px-1 font-medium text-green-700 ring-1 ring-inset ring-green-600/20"><?php echo esc_html( $options->label_for_coach ); ?></span>
                  <span class="text-gray-400 pl-2"x-text="formatChatTime(conversation)"></span>
                </div>
              </li>
            </template>
          </ul>
        </div>

        <!-- Typebox Message -->
        <div class="bg-gray-50 px-4 py-6 sm:px-6">
          <div class="flex space-x-3">
            <div class="min-w-0 flex-1">
              <form @submit.prevent="handleSendMessage" class="relative flex-auto">
                <div class="overflow-hidden bg-white rounded-lg pb-10 shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-1 focus-within:ring-darkblue-50">
                  <label for="comment" class="sr-only"><?php echo esc_html_x('Add your comment', 'ui', 'memberpress-coachkit'); ?></label>
                  <textarea x-model="newMessage.text" @input="autoExpand" @keydown.enter.prevent="handleSendMessage" @keydown.shift.enter.prevent="addNewLine" rows="1" name="comment" id="comment" class="block w-full resize-none border-0 bg-transparent p-1.5 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 overflow-y resize-none h-11 outline-none max-h-64" placeholder="Add your comment..."></textarea>
                  <!-- FilePond container -->
                  <div x-show="showFilePond" class="w-full">
                    <input type="file" class="filepond" name="message_attachments" multiple data-allow-reorder="true" data-max-file-size="3MB" data-max-files="3" x-ref="fileInput" @change="initializeFilePond" accept=".mp4, image/jpeg, image/gif, image/png, .doc, .docx, .rtf, .txt, .odt, .xls, .xlsx, .csv, .ods, .ots, .pdf, .zip, .ppt, .pptx, .pages, .numbers, .key">
                  </div>
                </div>

                <div class="absolute w-full bottom-0 flex justify-between pb-2 pl-3 pr-2">
                  <div class="flex items-center space-x-5">
                    <div class="flex items-center">
                      <button @click="openFileBrowser" type="button" class="-m-2.5 flex h-10 w-10 items-center justify-center rounded-full text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                          <path fill-rule="evenodd" d="M15.621 4.379a3 3 0 00-4.242 0l-7 7a3 3 0 004.241 4.243h.001l.497-.5a.75.75 0 011.064 1.057l-.498.501-.002.002a4.5 4.5 0 01-6.364-6.364l7-7a4.5 4.5 0 016.368 6.36l-3.455 3.553A2.625 2.625 0 119.52 9.52l3.45-3.451a.75.75 0 111.061 1.06l-3.45 3.451a1.125 1.125 0 001.587 1.595l3.454-3.553a3 3 0 000-4.242z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="sr-only"><?php echo esc_html_x( 'Attach a file', 'ui', 'memberpress-coachkit' ); ?></span>
                      </button>
                    </div>
                    <div class="flex items-center">
                    </div>
                  </div>
                  <button @click="handleSendMessage" type="button" class="mpch-button"><?php echo esc_html_x( 'Send', 'ui', 'memberpress-coachkit' ); ?></button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>

      <!-- Empty State -->
      <template x-if="emptyState">
        <div class="w-full h-full flex flex-col justify-center items-center" x-cloak>
          <div class="text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto h-12 w-12 text-gray-300">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7.875 14.25l1.214 1.942a2.25 2.25 0 001.908 1.058h2.006c.776 0 1.497-.4 1.908-1.058l1.214-1.942M2.41 9h4.636a2.25 2.25 0 011.872 1.002l.164.246a2.25 2.25 0 001.872 1.002h2.092a2.25 2.25 0 001.872-1.002l.164-.246A2.25 2.25 0 0116.954 9h4.636M2.41 9a2.25 2.25 0 00-.16.832V12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 12V9.832c0-.287-.055-.57-.16-.832M2.41 9a2.25 2.25 0 01.382-.632l3.285-3.832a2.25 2.25 0 011.708-.786h8.43c.657 0 1.281.287 1.709.786l3.284 3.832c.163.19.291.404.382.632M4.5 20.25h15A2.25 2.25 0 0021.75 18v-2.625c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125V18a2.25 2.25 0 002.25 2.25z" />
            </svg>

            <h3 class="mt-2 text-sm font-semibold text-gray-900"><?php echo esc_html_x( 'New Message', 'ui', 'memberpress-coachkit' ); ?></h3>
            <p class="mt-1 text-sm text-gray-500"><?php echo esc_html_x( 'You can send direct messages and files using Messages.', 'ui', 'memberpress-coachkit' ); ?></p>
          </div>


          <div class="mt-6">
            <div x-data="tooltip" x-init="initPopper()" class="bg-gradient-to-tr p-1 rounded-full">
              <div x-ref="popperButton">
                <button type="button" class="mpch-button">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="-ml-0.5 mr-1.5 h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                  </svg>
                  <?php echo esc_html_x( 'New Message', 'ui', 'memberpress-coachkit' ); ?>
                </button>
              </div>
              <?php mpch_load_template( 'theme/partials/new-chat-dropdown' ); ?>
            </div>
          </div>
        </div>
      </template>

    </div>
  </div>
  <?php
  echo $view->snackbar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  ?>
</div>


