<div class="sticky top-0 z-40 flex h-[74px] shrink-0 items-center gap-4 bg-darkblue px-4 sm:gap-x-6 sm:px-6 lg:px-4 lg:py-2 mpch-navbar mpch-site-header">
  <button type="button" class="-m-2.5 p-2.5 lg:hidden profile-menu__text" @click="open = true">
    <span class="sr-only"><?php esc_html_x( 'Open sidebar', 'ui', 'memberpress-coachkit' ); ?></span>
    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"></path>
    </svg>
  </button>

  <!-- Separator -->
  <div class="h-6 w-px bg-gray-900/10 lg:hidden" aria-hidden="true"></div>

  <div class="flex flex-1 gap-4 self-stretch lg:gap-6">
    <div class="relative flex flex-1" action="#" method="GET"></div>
    <div class="flex items-center gap-4 lg:gap-3">
      <a href="<?php echo esc_url( $notifications_url ); ?>" class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500">
        <span class="sr-only"><?php esc_html_x( 'View notifications', 'ui', 'memberpress-coachkit' ); ?></span>
        <svg class="h-6 w-6 profile-menu__text--small" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"></path>
        </svg>
      </a>

      <!-- Separator -->
      <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-900/10 separator" aria-hidden="true"></div>

      <!-- Profile dropdown -->
      <div x-data="menu({ open: false })" x-init="init()" @keydown.escape.stop="open = false; focusButton()" @click.away="onClickAway($event)" class="relative ml-3">
        <div>
          <button @click="onButtonClick()" type="button" class="-m-1.5 flex items-center p-2 space-x-4 hover:bg-black/40 rounded-md" id="user-menu-button" x-ref="button">
            <img class="h-10 w-10 rtl:ml-2 rounded-full bg-gray-50" src="<?php echo esc_url( $photo_url ); ?>" alt="">

            <span class="hidden lg:flex lg:flex-col truncate items-start space-y-1.5 profile-menu__text">
              <span class="leading-none" aria-hidden="true"><?php echo esc_html( $name ); ?></span>
              <span class="text-xs profile-menu__text--small"><?php echo esc_html( $email ); ?></span>
            </span>

            <svg class="w-6 h-6 profile-menu__arrow_down" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
          </button>
        </div>

        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 rtl:left-0 rtl:right-auto z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" x-ref="menu-items" x-bind:aria-activedescendant="activeDescendant" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1" @keydown.arrow-up.prevent="onArrowUp()" @keydown.arrow-down.prevent="onArrowDown()" @keydown.tab="open = false" @keydown.enter.prevent="open = false; focusButton()" @keyup.space.prevent="open = false; focusButton()" x-cloak>
          <a href="<?php echo esc_url( $profile_url ); ?>" class="block px-4 py-2 " x-state:on="Active" x-state:off="Not Active" :class="{ 'bg-gray-100': activeIndex === 0 }" role="menuitem" tabindex="-1" id="user-menu-item-0" @mouseenter="onMouseEnter($event)" @mousemove="onMouseMove($event, 0)" @mouseleave="onMouseLeave($event)" @click="open = false; focusButton()"><?php esc_html_e( 'Account', 'memberpress-coachkit' ); ?></a>
          <a href="<?php echo esc_url( $logout_url ); ?>" class="block px-4 py-2 " :class="{ 'bg-gray-100': activeIndex === 2 }" role="menuitem" tabindex="-1" id="user-menu-item-2" @mouseenter="onMouseEnter($event)" @mousemove="onMouseMove($event, 2)" @mouseleave="onMouseLeave($event)" @click="open = false; focusButton()"><?php esc_html_e( 'Log out', 'memberpress-coachkit' ); ?></a>
        </div>

      </div>

    </div>
  </div>
</div>
