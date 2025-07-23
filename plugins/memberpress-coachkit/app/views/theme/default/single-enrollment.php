<?php
/**
 * The template for displaying all enrollments
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package coaching
 */

?>

<div class="mpch-container alignwide h-full flex flex-col">

  <?php if ( $view->is_readylaunch ) : ?>
    <div class="px-4 sm:px-6 lg:px-8" x-data="tabs" @tab-click.window="onTabClick" @tab-keydown.window="onTabKeydown">
    <?php else : ?>
    <div class="max-w-5xl mx-auto py-10" x-data="tabs" @tab-click.window="onTabClick" @tab-keydown.window="onTabKeydown">

      <?php
      if ( $view->enrollment->group->starts_in_future ) { ?>
        <div class="bg-yellow-50 border border-yellow-500 rounded-md text-yellow-900 px-4 py-3 shadow-sm mb-6" role="alert">
          <div class="flex">
            <div class="py-1"><svg class="fill-current h-6 w-6 text-yellow-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z" />
              </svg></div>
            <div>
              <p class="font-bold m-0"><?php esc_html_e( 'Program Commencing Soon', 'memberpress-coachkit' ); ?></p>
              <p class="text-sm m-0"><?php printf( '%s <strong>%s</strong>.', esc_html__( 'The program is scheduled to begin on', 'memberpress-coachkit' ), esc_html( $view->enrollment->group->start_date ) ); ?></p>
            </div>
          </div>
        </div>
      <?php } ?>

    <?php endif; ?>

      <div class="lg:flex lg:items-center lg:justify-between" @download-file.window="$store.coaching.download($event.detail)">
        <div class="min-w-0 flex-1">
          <h1 class="text-2xl font-semibold leading-6 text-gray-900 m-0"><?php echo esc_html( $view->enrollment->title ); ?></h1>
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
            <div class="mt-2 flex items-center text-sm text-gray-500">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class=" h-5 w-5 flex-shrink-0 text-gray-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>

              <span class="mx-1.5"><?php printf( '%s: %s', $options->label_for_coach, esc_html( $view->enrollment->coach ) ); ?></span>

              <?php if ($view->enrollment->group->appointment_url) : ?><a class="inline-flex space-x-1 items-center rounded-md bg-blue-800 px-2 py-1.5 text-xs font-medium text-white ring-1 ring-inset ring-blue-700/10" href="<?php echo esc_url( $view->enrollment->group->appointment_url ); ?>" target="_blank">
                <svg width="14" height="16" viewBox="0 0 14 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M4.75 2H9.25V0.75C9.25 0.34375 9.5625 0 10 0C10.4062 0 10.75 0.34375 10.75 0.75V2H12C13.0938 2 14 2.90625 14 4V14C14 15.125 13.0938 16 12 16H2C0.875 16 0 15.125 0 14V4C0 2.90625 0.875 2 2 2H3.25V0.75C3.25 0.34375 3.5625 0 4 0C4.40625 0 4.75 0.34375 4.75 0.75V2ZM1.5 7.75H4V6H1.5V7.75ZM1.5 9.25V11.25H4V9.25H1.5ZM5.5 9.25V11.25H8.5V9.25H5.5ZM10 9.25V11.25H12.5V9.25H10ZM12.5 6H10V7.75H12.5V6ZM12.5 12.75H10V14.5H12C12.25 14.5 12.5 14.2812 12.5 14V12.75ZM8.5 12.75H5.5V14.5H8.5V12.75ZM4 12.75H1.5V14C1.5 14.2812 1.71875 14.5 2 14.5H4V12.75ZM8.5 6H5.5V7.75H8.5V6Z" fill="#ffffff"/>
                </svg>
                <span><?php esc_html_e( 'Schedule Appointment', 'memberpress-coachkit' ) ?></span>
              </a> <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end mt-14">
        <span class="isolate inline-flex rounded-md shadow-sm">
          <button x-data="tab(0)" x-on:click="onClick" @keydown="onKeydown" @tab-select.window="onTabSelect" x-init="init()" type="button" class="relative inline-flex items-center gap-0.5 rounded-l-md px-3 py-2 text-sm font-semibold text-darkblue-50 ring-1 ring-inset ring-darkblue-50 hover:bg-gray-50 focus:z-10" :tabindex="selected ? 0 : -1" :class="{'bg-darkblue-50 bg-opacity-5': selected}">
            <span x-cloak x-show="selected" class="dashicons dashicons-yes"></span>
            <?php esc_html_e( 'Milestones', 'memberpress-coachkit' ); ?>
          </button>
          <button x-data="tab(0)" x-on:click="onClick" @keydown="onKeydown" @tab-select.window="onTabSelect" x-init="init()" type="button" class="relative -ml-px inline-flex gap-0.5 items-center rounded-r-md px-3 py-2 text-sm font-semibold text-darkblue-50 ring-1 ring-inset ring-darkblue-50 hover:bg-gray-50 focus:z-10" :tabindex="selected ? 0 : -1" :class="{'bg-darkblue-50 bg-opacity-5': selected}">
            <span x-cloak x-show="selected" class="dashicons dashicons-yes"></span>
            <?php esc_html_e( 'Habits', 'memberpress-coachkit' ); ?>
          </button>
        </span>
      </div>

      <!-- Tab panels -->
      <div>
        <?php
        mpch_load_template('theme/partials/enrollment-milestones', array(
          'milestones'       => $view->enrollment->milestones,
          'enrollment_id'    => $view->enrollment->id,
          'starts_in_future' => $view->enrollment->group->starts_in_future,
        ));

        mpch_load_template('theme/partials/enrollment-habits', array(
          'habits'        => $view->enrollment->habits,
          'enrollment_id' => $view->enrollment->id,
          'arrow_url'     => $view->arrow_url,
        ));
        ?>
      </div>
    </div>


    <?php if ( ! $view->is_readylaunch ) {
      echo $view->snackbar;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
