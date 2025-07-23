<table class="min-w-full divide-y divide-gray-300">
  <thead class="bg-white">
    <tr>
      <th scope="col" class="py-3.5 pl-4 pr-3 text-left rtl:text-right text-sm font-semibold text-gray-900 sm:pl-6"><?php echo esc_html_e( 'Name', 'memberpress-coachkit' ); ?></th>
      <th scope="col" class="px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900"><?php echo esc_html_e( 'Email', 'memberpress-coachkit' ); ?></th>
      <th scope="col" class="px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900"><?php echo esc_html_e( 'Programs', 'memberpress-coachkit' ); ?></th>
      <th scope="col" class="px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900"><?php echo esc_html_e( 'Last Active', 'memberpress-coachkit' ); ?></th>
    </tr>
  </thead>
  <tbody class="divide-y divide-gray-200 bg-white">
    <template x-for="(student, index) in students" :key="index">
      <tr>
        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
          <div class="flex items-center space-x-2">
            <a class="text-darkblue-50" :href="student.public_url" x-text="student.name"></a>
            <span x-show="student.off_track" class="bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded"><?php echo esc_html_e( 'Off Track', 'memberpress-coachkit' ); ?></span>
          </div>
        </td>
        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-text="student.email"></td>
        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-text="student.programs"></td>
        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
          <div class="flex items-center space-x-4">
            <span class="mpch-progress__percent" x-text="student.last_active"></span>
          </div>
        </td>
      </tr>
    </template>
  </tbody>
</table>
