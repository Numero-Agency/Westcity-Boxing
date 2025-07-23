<div x-ref="popperElement" class="hidden">
  <div x-data="contacts">
    <div x-show="contactsNotEmpty">
      <div x-show="userIsCoach" class="border-b border-gray-200">
        <nav class="-mb-px flex" aria-label="Tabs">
          <a @click="switchTab('student')" href="#" class="border-transparent text-gray-500 hover:border-gray-300 w-full hover:text-gray-700 border-b-2 py-2 px-1 text-center text-sm font-medium" x-ref="studentsTab"><?php echo esc_html_x( "Students", 'ui', 'memberpress-coachkit' ); ?></a>
          <a @click="switchTab('group')" href="#" class="border-transparent text-gray-500 hover:border-gray-300 w-full hover:text-gray-700 border-b-2 py-2 px-1 text-center text-sm font-medium" x-ref="groupsTab"><?php echo esc_html_x( "Cohorts", 'ui', 'memberpress-coachkit' ); ?></a>
        </nav>
      </div>
    </div>

    <div x-show="contactsNotEmpty" class="py-3">
      <label for="input-group-search" class="sr-only"><?php echo esc_html_x( "Search", 'ui', 'memberpress-coachkit' ); ?></label>
      <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"></path>
          </svg>
        </div>
        <input type="text" x-model.debounce="searchContactsQuery" id="input-group-search" class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" placeholder="Search">
      </div>
    </div>

    <ul x-show="contactsNotEmpty" class="h-48 pb-3 overflow-y-auto text-sm text-gray-700 m-0 p-0" aria-labelledby="dropdownSearchButton">
      <template x-for="contact in filteredContacts">
        <li @click="OpenOrCreateRoom(contact), hideTooltip()" class="cursor-pointer">
          <div class="flex group items-center pl-2 rounded hover:bg-gray-100 ">
            <img class="h-7 w-7 rounded-full" :src="contact.avatar_url" alt="" x-show="contact.avatar_url">

            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border-none bg-black text-[0.625rem] font-medium text-white" x-text="contact.name[0]" x-show="!contact.avatar_url"></span>

            <div class="py-2 ml-2">
              <label for="checkbox-item-12" class="w-full text-sm font-medium text-gray-900 group-hover:text-darkblue-50 rounded cursor-pointer" x-text="`${contact.name}`"></label>
              <p class="truncate text-xs text-gray-500 group-hover:text-darkblue-50 m-0" x-text="contact.post_title"></p>
            </div>
          </div>
        </li>
      </template>
    </ul>

    <p x-show="contactsEmpty"><?php echo esc_html_x( "Sorry, you can't send messages. Either you're not enrolled in a program, or your current enrollment doesn't allow messaging.", 'ui', 'memberpress-coachkit' ); ?></p>
  </div>
</div>
