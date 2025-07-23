<div class="mpch-container alignwide">
  <?php if ( $view->is_readylaunch ) : ?>
  <div class="px-4 sm:px-6 lg:px-8">
    <?php else : ?>
  <div class="max-w-5xl mx-auto py-10">
  <?php endif; ?>
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-2xl font-semibold leading-6 text-gray-900"><?php echo esc_html_e( 'Enrollments', 'memberpress-coachkit' ); ?></h1>
        <p class="mt-2 text-sm text-gray-700"><?php echo esc_html_e( 'A list of all your enrollments including their milestones and progress.', 'memberpress-coachkit' ); ?></p>
      </div>
    </div>
    <div class="mt-8 flow-root">
      <div class="-mx-4 -my-2 overflow-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <?php
            if ( empty( $view->enrollments ) ) {
              printf( '<p class="bg-white p-3 text-sm m-0">%s</p>', esc_html__( 'No enrollments found', 'memberpress-coachkit' ) );
            } else {
              mpch_load_template( 'theme/partials/enrollments-table', get_defined_vars() );
            }
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
