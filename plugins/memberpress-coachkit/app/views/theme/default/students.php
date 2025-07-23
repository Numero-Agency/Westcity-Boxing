<div x-data="{ open: false }" @keydown.window.escape="open = false" class="mpch-container alignwide">

  <?php if ($view->is_readylaunch) : ?>
  <div class="px-4 sm:px-6 lg:px-8">
  <?php else : ?>
  <div class="max-w-5xl mx-auto py-10">
  <?php endif; ?>

    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-2xl font-semibold leading-6 text-gray-900"><?php echo esc_html( $options->label_for_clients ); ?></h1>
        <p class="mt-2 text-sm text-gray-700"><?php printf( esc_html__( 'A list of all your %s.', 'memberpress-coachkit' ), esc_html( $options->label_for_clients ) ); ?></p>
      </div>
    </div>


    <?php if ($view->is_readylaunch) : ?>
      <div class="mt-2 justify-between py-4 bg-white" x-data="students_filter(<?php echo esc_html(wp_json_encode($view->groups)); ?>)">
    <?php else : ?>
      <div class="mt-2 justify-between py-4 px-4 bg-white" x-data="students_filter(<?php echo esc_html(wp_json_encode($view->groups)); ?>)">
    <?php endif; ?>

      <div class="flex items-center space-x-2">
        <template x-if="hasOneGroup" hidden>
          <p class="pr-2 text-gray-900 sm:text-sm sm:leading-6" x-text="`${group.title} (${group.program})`"></p>
        </template>
        <template x-if="hasManyGroups" hidden>
          <select class="block w-auto rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 sm:text-sm sm:leading-6 outline-none" x-model="groupId">
            <option value=""><?php echo esc_html__('All Cohorts', 'memberpress-coachkit'); ?></option>
            <template x-for="group in groups">
              <option :value="group.id" x-text="`${group.title} (${group.program})`"></option>
            </template>
          </select>
        </template>
        <button type="button" class="rounded-sm bg-white rtl:!mr-2 py-2 px-3 text-xs font-medium shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" x-show="groupId" @click="toggleApptModal" x-cloak><?php echo esc_html_e('Edit Settings', 'memberpress-coachkit'); ?></button>
      </div>
      <?php mpch_load_template('theme/partials/modal-appointments', get_defined_vars()) ?>
    </div>


    <div class="mt-8 flow-root" x-data="students(<?php echo esc_html(wp_json_encode($view->students)); ?>)">
      <div class="-mx-4 -my-2 overflow-auto sm:-mx-6 lg:-mx-8" x-show="!loadingStudents">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
          <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <template x-if="noStudents">
              <?php printf( '<p class="bg-white p-2 m-0">' . esc_html__( 'No %s found.', 'memberpress-coachkit' ) .'</p>', strtolower( esc_html( $options->label_for_clients ) ) ); ?>
            </template>
            <template x-if="hasStudents">
              <?php
              mpch_load_template('theme/partials/students-table', get_defined_vars());
              ?>
            </template>
          </div>
        </div>
      </div>
      <?php mpch_load_template('theme/partials/students-table-skeleton'); ?>
    </div>



  </div>
  <?php mpch_load_template('theme/partials/snackbar'); ?>
</div>
