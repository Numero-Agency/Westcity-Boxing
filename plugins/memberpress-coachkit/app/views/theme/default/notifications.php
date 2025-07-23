<!-- <div class=""> -->
<div x-data="{ open: false }" @keydown.window.escape="open = false" class="mpch-container alignwide px-4 sm:px-6 lg:px-8">
  <div class="max-w-5xl mx-auto py-10">

    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-2xl font-semibold leading-6 text-gray-900 m-0"><?php echo esc_html_e( 'Notifications', 'memberpress-coachkit' ); ?></h1>
        <p class="mt-2 text-sm text-gray-700"><?php echo esc_html_e( 'Timely Updates: Keeping you posted on important events.', 'memberpress-coachkit' ); ?></p>
      </div>
    </div>
    <div class="mt-8 flow-root">
      <!-- <div class="-mx-4 -my-2 overflow-auto sm:-mx-6 lg:-mx-8"> -->
      <div x-data="notifications(<?php echo esc_html( wp_json_encode( $view->notifications ) ); ?>)" class="grid grid-cols-8 sm:grid-cols-12 gap-12">
        <div class="col-span-8">

          <ul role="list" class="divide-y divide-gray-100 overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 rounded-md">
            <template x-for="notification in notifications.activities">
              <li class="relative flex justify-between gap-x-6 px-4 py-5 hover:bg-gray-50 sm:px-6">
                <div class="flex min-w-0 gap-x-4 w-full">
                  <img class="w-10" :src="getEventIcon(notification.event)"></img>
                  <div class="min-w-0 flex-auto">
                    <p class="text-sm font-semibold leading-6 text-gray-800 m-0">
                      <span href="#" x-html="notification.message">
                        <span class="absolute inset-x-0 -top-px bottom-0"></span>
                      </span>
                    </p>
                    <p class="flex text-xs leading-5 text-gray-500 m-0">
                      <span class="relative truncate hover:underline" x-text="notification.date"></span>
                    </p>
                  </div>
                </div>
                <div class="flex shrink-0 items-center gap-x-4">
                </div>
              </li>
            </template>
            <li x-show="!notifications.activities.length" class="p-2 text-sm" x-cloak><?php echo esc_html_e( 'No notifications found', 'memberpress-coachkit' ); ?></li>
          </ul>

          <nav x-show="notifications.activities.length" class="flex items-center justify-between pl-6 rtl:pr-6 border-t border-gray-200 bg-white py-3" aria-label="Pagination" x-cloak>
            <div class="hidden sm:block">
              <p class="text-sm text-gray-700">
                <?php printf( '%s <span class="font-medium" x-text="page">1</span> %s <span class="font-medium" x-text="notifications.max_pages"></span>', __('Showing page', 'memberpress-coachkit'), __('of', 'memberpress-coachkit') ); ?>
              </p>
            </div>
            <div class="flex flex-1 justify-between sm:justify-end">
              <a @click="prevPage" x-show="hasPrevPage" href="#" class="relative inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline-offset-0"><?php echo esc_html_e( 'Previous', 'memberpress-coachkit' ); ?></a>
              <a @click="nextPage" x-show="hasNextPage" href="#" class="relative ml-3 inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline-offset-0"><?php echo esc_html_e( 'Next', 'memberpress-coachkit' ); ?></a>
            </div>
          </nav>
        </div>


        <aside class="col-span-8 sm:col-span-4">
          <ul role="list" class="overflow-hidden bg-white shadow-sm ring-1 ring-gray-900/5 rounded-md p-6 space-y-2">
            <li>
              <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" x-model="filters.program" class="text-darkblue-50 rounded w-4 h-4">
                <span class="text-sm rtl:!mr-2"><?php echo esc_html_e( 'Programs', 'memberpress-coachkit' ); ?></span>
              </label>
            </li>
            <li>
              <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" x-model="filters.message" class="text-darkblue-50 rounded w-4 h-4">
                <span class="text-sm rtl:!mr-2"><?php echo esc_html_e( 'Messages', 'memberpress-coachkit' ); ?></span>
              </label>
            </li>
            <li>
              <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" x-model="filters.milestone" class="text-darkblue-50 rounded w-4 h-4">
                <span class="text-sm rtl:!mr-2"><?php echo esc_html_e( 'Milestones', 'memberpress-coachkit' ); ?></span>
              </label>
            </li>
            <li>
              <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" x-model="filters.habit" class="text-darkblue-50 rounded w-4 h-4">
                <span class="text-sm rtl:!mr-2"><?php echo esc_html_e( 'Habits', 'memberpress-coachkit' ); ?></span>
              </label>
            </li>
          </ul>
        </aside>


      </div>

    </div>
  </div>
</div>
