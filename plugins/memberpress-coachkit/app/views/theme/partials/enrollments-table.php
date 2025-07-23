<table x-data="accordion({ open: [] })" class="min-w-full divide-y divide-gray-300">
  <thead class="bg-white">
    <tr>
      <th scope="col" class="py-3.5 pl-4 pr-3 text-left rtl:text-right text-sm font-semibold text-gray-900 sm:pl-6"><?php echo esc_html_e( 'Enrollments', 'memberpress-coachkit' ); ?></th>
      <th scope="col" class="px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900"><?php echo esc_html_e( 'Started', 'memberpress-coachkit' ); ?></th>
      <th scope="col" class="px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900"><?php echo $options->label_for_coach; ?></th>
      <th scope="col" class="px-3 py-3.5 text-left rtl:text-right text-sm font-semibold text-gray-900"><?php echo esc_html_e( 'Progress', 'memberpress-coachkit' ); ?></th>
    </tr>
  </thead>
  <tbody class="divide-y divide-gray-200 bg-white">
    <?php foreach ( $view->enrollments as $index => $enrollment ) : ?>
      <tr>
        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
          <div class="flex items-center space-x-2">
            <button x-on:click="toggle(<?php echo esc_attr( $index ); ?>)" type="button">
              <!-- Right Arrow -->
              <svg x-show="!open.includes(<?php echo esc_attr( $index ); ?>)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 align-middle">
                <path fill-rule="evenodd" d="M16.28 11.47a.75.75 0 010 1.06l-7.5 7.5a.75.75 0 01-1.06-1.06L14.69 12 7.72 5.03a.75.75 0 011.06-1.06l7.5 7.5z" clip-rule="evenodd" />
              </svg>

              <!-- Down Arrow -->
              <svg x-show="open.includes(<?php echo esc_attr( $index ); ?>)" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 align-middle" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
              </svg>
            </button>

            <a class="text-darkblue-50" href="<?php echo esc_url( $enrollment->public_url ); ?>"><?php echo esc_html( $enrollment->title ); ?></a>
          </div>
        </td>
        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?php echo esc_html( $enrollment->started ); ?></td>
        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 flex item-center space-x-2">
          <span><?php echo esc_html( $enrollment->coach ); ?></span>
          <?php if ($enrollment->appointment_url) : ?><a href="<?php echo esc_url( $enrollment->appointment_url ); ?>" target="_blank"><img src="<?php echo esc_url(mpch_images('calendar.svg')) ?>" class="w-4 h-4" alt=""></a> <?php endif; ?>
        </td>
        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
          <div class="flex items-center space-x-4">
            <div class="mpch-progress">
              <div class="mpch-progress__bar --success" style="width: <?php echo esc_attr( $enrollment->progress ); ?>%;"></div>
              <!-- <div class="mpch-progress__bar --success" style="width: 0%;"></div> -->
            </div>
            <!-- <span class="mpch-progress__percent">0%</span> -->
            <span class="mpch-progress__percent"><?php printf( '%s%%', esc_attr( $enrollment->progress ) ); ?></span>
          </div>
        </td>
      </tr>
      <?php foreach ( $enrollment->milestones as $i => $milestone ) : ?>
        <tr x-show="open.includes(<?php echo esc_attr( $index ); ?>)" x-cloak>
          <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6" colspan="3">
            <div class="flex items-center pl-10">
              <?php echo esc_html( $milestone->title ); ?>
            </div>
          </td>
          <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?php echo esc_html( $milestone->status ); ?></td>
        </tr>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </tbody>
</table>
