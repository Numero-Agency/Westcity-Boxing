<div x-show="open" class="relative z-50 lg:hidden" x-description="Off-canvas menu for mobile, show/hide based on off-canvas menu state." x-ref="dialog" aria-modal="true" style="display: none;">

  <div x-show="open" x-transition:enter="x-transition-opacity x-ease-linear x-duration-300" x-transition:enter-start="x-opacity-0" x-transition:enter-end="x-opacity-100" x-transition:leave="x-transition-opacity x-ease-linear x-duration-300" x-transition:leave-start="x-opacity-100" x-transition:leave-end="x-opacity-0" class="fixed inset-0 bg-gray-900/80" x-description="Off-canvas menu backdrop, show/hide based on off-canvas menu state." style="display: none;"></div>


  <div class="fixed inset-0 flex">
    <div x-show="open" x-transition:enter="x-transition x-ease-in-out x-duration-300 x-transform" x-transition:enter-start="x--translate-x-full" x-transition:enter-end="x-translate-x-0" x-transition:leave="x-transition x-ease-in-out x-duration-300 x-transform" x-transition:leave-start="x-translate-x-0" x-transition:leave-end="x--translate-x-full" x-description="Off-canvas menu, show/hide based on off-canvas menu state." class="relative ltr:mr-16 rtl:ml-16 flex w-full max-w-xs flex-1" @click.away="open = false" style="display: none;">

      <div x-show="open" x-transition:enter="x-ease-in-out x-duration-300" x-transition:enter-start="x-opacity-0" x-transition:enter-end="x-opacity-100" x-transition:leave="x-ease-in-out x-duration-300" x-transition:leave-start="x-opacity-100" x-transition:leave-end="x-opacity-0" x-description="Close button, show/hide based on off-canvas menu state." class="absolute left-full top-0 flex w-16 justify-center pt-5" style="display: none;">
        <button type="button" class="-m-2.5 p-2.5" @click="open = false">
          <span class="sr-only">Close sidebar</span>
          <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- MOBILE Sidebar component, see desktop sidebar below -->
      <div class="flex-grow flex-col gap-y-5 overflow-y-auto bg-darkblue px-6 pb-4 mpch-account-nav">
        <div class="flex h-16 shrink-0 items-center">
          <a href="<?php echo esc_url( home_url() ); ?>">
            <img class="h-8 w-auto" src="<?php echo esc_url( $logo_url ); ?>" alt="Your Company">
          </a>
        </div>
        <nav class="flex flex-1 flex-col">
          <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
              <ul role="list" class="-mx-2 space-y-1">
                <li>
                  <a href="<?php echo esc_url( $profile_url ); ?>" class="bg-opacity-50 text-white  group flex gap-2.5 p-2.5 leading-6 <?php echo esc_attr( $active_class( 'coaching' ) ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-[1.3rem] mx-[3px] shrink-0" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
                    </svg>
                    <?php esc_html_e( 'Account', 'memberpress-coachkit' ); ?>
                  </a>
                </li>
                <?php if ( $is_student ) : ?>
                <li>
                  <a href="<?php echo esc_url( $enrollment_url ); ?>" class="bg-opacity-50 text-white  group flex gap-2.5 p-2.5 leading-6 <?php echo esc_attr( $active_class( 'coaching_enrollments' ) ); ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-[1.3rem] mx-[3px] shrink-0"  fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917l-7.5-3.5ZM8 8.46 1.758 5.965 8 3.052l6.242 2.913L8 8.46Z"/>
                  <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466 4.176 9.032Zm-.068 1.873.22-.748 3.496 1.311a.5.5 0 0 0 .352 0l3.496-1.311.22.748L8 12.46l-3.892-1.556Z"/>
                </svg>
                    <?php esc_html_e( 'Enrollments', 'memberpress-coachkit' ); ?>
                  </a>
                </li>
                <?php elseif ( $is_coach ) : ?>
                <li>
                  <a href="<?php echo esc_url( $students_url ); ?>" class="bg-opacity-50 text-white  group flex gap-2.5 p-2.5 leading-6 <?php echo esc_attr( $active_class( 'coaching_students' ) ); ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-5 w-[1.3rem] mx-[3px]" viewBox="0 0 16 16">
                    <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                  </svg>
                    <?php esc_html_e( 'Students', 'memberpress-coachkit' ); ?>
                  </a>
                </li>
                <?php endif; ?>
                <?php if ( $enable_messaging ) : ?>
                <li>
                  <a href="<?php echo esc_url( $messages_url ); ?>" class="bg-opacity-50 text-white  group flex items-center gap-2.5 p-2.5 leading-6 <?php echo esc_attr( $active_class( 'coaching_messages' ) ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-5 w-[1.3rem] mx-[3px]" viewBox="0 0 16 16">
                      <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-2.5a2 2 0 0 0-1.6.8L8 14.333 6.1 11.8a2 2 0 0 0-1.6-.8H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2.5a1 1 0 0 1 .8.4l1.9 2.533a1 1 0 0 0 1.6 0l1.9-2.533a1 1 0 0 1 .8-.4H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                      <path d="M3 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM3 6a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 6zm0 2.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                    </svg>
                    <?php esc_html_e( 'Messages', 'memberpress-coachkit' ); ?>
                  </a>
                </li>
                <?php endif; ?>
              </ul>
            </li>
            <li class="mt-auto">
              <?php if ( $groups ) { ?>
                <div class="text-xs px-2 font-semibold leading-6"><?php esc_html_e( 'Cohorts', 'memberpress-coachkit' ); ?></div>
              <?php } ?>
              <ul role="list" class="-mx-2 mt-2 space-y-1">
                <?php
                foreach ( $groups as $group ) { ?>
                  <li class="text-gray-100 hover:text-white group flex gap-2.5 p-2 text-sm leading-6 font-semibold">
                    <a class="text-gray-100 hover:text-white group flex gap-2.5 p-2 text-sm leading-6 font-semibold" href="<?php echo esc_url( $group->url ); ?>">
                      <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border border-indigo-400 bg-indigo-500 text-[0.625rem] font-medium text-white"><?php echo esc_html( substr( $group->title, 0, 1 ) ); ?></span>
                      <span class="truncate"><?php echo esc_html( $group->title ); ?></span>
                    </a>
                    <p class="truncate text-xs text-gray-500 group-hover:text-darkblue-50 m-0"><?php echo esc_html( $group->program ); ?></p>
                  </li>
                <?php }
                ?>
              </ul>
            </li>
          </ul>
        </nav>
      </div>
    </div>

  </div>
</div>


<!-- Static sidebar for desktop -->
<div id="mpch-account-nav" class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col">
  <!-- Sidebar component, swap this element with another sidebar if you like -->
  <div class="flex flex-grow flex-col overflow-y-auto bg-darkblue pb-4 mpch-account-nav">
    <div class="flex h-[74px] shrink-0 items-center px-4">
      <a href="<?php echo esc_url( home_url() ); ?>">
        <img class="h-auto w-auto max-h-[40px]" src="<?php echo esc_url( $logo_url ); ?>">
      </a>
    </div>
    <nav class="flex flex-1 flex-col">
      <ul role="list" class="flex flex-1 flex-col gap-y-7">
        <li>
          <ul role="list" class="space-y-1">
            <li>
              <a href="<?php echo esc_url( $profile_url ); ?>" class="bg-opacity-50 text-white  group flex gap-2.5 p-2.5 leading-6 <?php echo esc_attr( $active_class( 'coaching' ) ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-[1.3rem] mx-[3px] shrink-0" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
                </svg>
                <?php esc_html_e( 'Account', 'memberpress-coachkit' ); ?>
              </a>
            </li>
            <?php if ( $is_student ) : ?>
            <li>
              <a href="<?php echo esc_url( $enrollment_url ); ?>" class="text-white bg-opacity-50 hover:text-white group flex items-center gap-2.5 p-2.5 leading-6 <?php echo esc_attr( $active_class( 'coaching_enrollments' ) ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-[1.3rem] mx-[3px] shrink-0"  fill="currentColor" viewBox="0 0 16 16">
                  <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917l-7.5-3.5ZM8 8.46 1.758 5.965 8 3.052l6.242 2.913L8 8.46Z"/>
                  <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466 4.176 9.032Zm-.068 1.873.22-.748 3.496 1.311a.5.5 0 0 0 .352 0l3.496-1.311.22.748L8 12.46l-3.892-1.556Z"/>
                </svg>
                <?php esc_html_e( 'Enrollments', 'memberpress-coachkit' ); ?>
              </a>
            </li>
            <?php elseif ( $is_coach ) : ?>
            <li>
              <a href="<?php echo esc_url( $students_url ); ?>" class="text-white bg-opacity-50 hover:text-white group flex gap-2.5 p-2.5 leading-6 <?php echo esc_attr( $active_class( 'coaching_students' ) ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="h-5 w-[1.3rem] mx-[3px]" viewBox="0 0 16 16">
                  <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                </svg>
                <?php echo esc_html( $options->label_for_clients ); ?>
              </a>
            </li>
            <?php endif; ?>
            <?php if ( $enable_messaging ) : ?>
            <li>
              <a href="<?php echo esc_url( $messages_url ); ?>" class="bg-opacity-50 text-white items-center group flex gap-2.5 p-2.5 leading-6 <?php echo esc_attr( $active_class( 'coaching_messages' ) ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="h-5 w-[1.3rem] mx-[3px] shrink-0" viewBox="0 0 16 16">
                  <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-2.5a2 2 0 0 0-1.6.8L8 14.333 6.1 11.8a2 2 0 0 0-1.6-.8H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2.5a1 1 0 0 1 .8.4l1.9 2.533a1 1 0 0 0 1.6 0l1.9-2.533a1 1 0 0 1 .8-.4H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                  <path d="M3 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM3 6a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 6zm0 2.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                </svg>
                <?php esc_html_e( 'Messages', 'memberpress-coachkit' ); ?>
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </li>
        <li class="mt-auto">
          <?php if ( $groups ) { ?>
            <div class="text-xs px-2 font-semibold leading-6 "><?php esc_html_e( 'Cohorts', 'memberpress-coachkit' ); ?></div>
          <?php } ?>
          <ul role="list" class="mt-2 space-y-1">
            <?php
            foreach ( $groups as $group ) { ?>
              <li class="">
                <a class="text-gray-100 hover:text-white group flex items-center gap-2.5 p-2 text-sm leading-6 font-semibold" href="<?php echo esc_url( $group->url ); ?>">
                  <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border border-indigo-400 bg-indigo-500 text-[0.625rem] font-medium text-white"><?php echo esc_html( substr( $group->title, 0, 1 ) ); ?></span>
                  <div class="leading-tight">
                    <span class="truncate"><?php printf( '%s', esc_html( $group->title ) ); ?></span>
                    <p class="truncate text-xs font-normal text-gray-500 group-hover:text-darkblue-50 m-0"><?php echo esc_html( $group->program ); ?></p>
                  </div>
                </a>
              </li>
            <?php }
            ?>

          </ul>
        </li>
      </ul>
    </nav>
  </div>
</div>
