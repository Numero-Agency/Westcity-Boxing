<div x-data x-cloak x-show="$store.snackbar.open" aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end justify-end px-4 py-6 sm:p-6 z-50" x-transition:enter="x-transition x-ease-out x-duration-300 x-transform" x-transition:enter-start="x-translate-y-2 x-opacity-0 sm:x-translate-y-0 sm:x-translate-x-2" x-transition:enter-end="x-translate-y-0 x-opacity-100 sm:x-translate-x-0" x-transition:leave="x-transition x-ease-in x-duration-100" x-transition:leave-start="x-opacity-100" x-transition:leave-end="x-opacity-0">
  <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
    <!-- Notification panel, dynamically insert this into the live region when it needs to be displayed -->
    <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
      <div class="p-4">
        <div class="flex items-start">
          <div x-cloak x-show="'error' === $store.snackbar.type" class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-red-500 bg-red-100 rounded-lg ">
            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Error icon</span>
          </div>

          <div x-cloak x-show="'success' === $store.snackbar.type" class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-green-500 bg-green-100 rounded-lg ">
            <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Success icon</span>
          </div>

          <div class="ml-3 w-0 flex-1 pt-0.5">
            <p x-show="'success' === $store.snackbar.type" class="text-sm font-medium text-gray-900 m-0"><?php esc_html_e( 'Success', 'memberpress-coachkit' ); ?></p>
            <p x-show="'error' === $store.snackbar.type" class="text-sm font-medium text-gray-900 m-0"><?php esc_html_e( 'Error', 'memberpress-coachkit' ); ?></p>
            <p class="mt-1 text-sm text-gray-500 mb-0" x-html="$store.snackbar.message"></p>
          </div>
          <div class="ml-4 flex flex-shrink-0">
            <button type="button" @click="$store.snackbar.close()" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
              <span class="sr-only">Close</span>
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

