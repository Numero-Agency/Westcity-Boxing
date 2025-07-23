jQuery(document).ready(function ($) {
  var popperInstances = [];
  var $buttons = $(document).find('[data-toggle="popper"], [data-hover="popper"]');
  var $contents = $('[data-content="popper"]');

  function initialize() {
    $buttons.each(bindPopperToElements)
    $(document).on('click', handleClickedTrigger);
    $(document).on('mouseenter', '[data-hover="popper"]', handleHoverTrigger);
    $(document).on('mouseleave', '[data-hover="popper"]', handleHoverTrigger);
    $(document).on('close-tooltip', reInitializePoppers);
    $(document).on('milestone-added', reInitializePoppers);
    $(document).on('habit-added', reInitializePoppers);
    $(document).on('milestone-refreshed', reInitializePoppers);
    $(document).on('init-popper', reInitializePoppers);
  }

  /**
   * Kills all Popper instances
   * Gets all buttons and contents, in case new one has been added or removed
   * Create fresh Popper instances and attach same to buttons
   */
  function reInitializePoppers() {
    hidePopperAll($contents, true);
    popperInstances = [];
    $buttons = $(document).find('[data-toggle="popper"]');
    $contents = $('[data-content="popper"]');
    $buttons.each(bindPopperToElements)
  }

  /**
   * Create Popper instances for all available triggers
   */
  function bindPopperToElements() {
    var $trigger = $(this);
    var $content = $(this).parent().find('[data-content="popper"]');

    var PopperInstance = Popper.createPopper($trigger.get(0), $content.get(0), {
      placement: $content.data('placement') || 'top',
      modifiers: [
        {
          name: 'offset',
          options: {
            offset: [0, 8],
          },
        },
      ],
    });

    popperInstances.push(PopperInstance)
  }

  /**
   * Handles opening / closing of popovers
   * @param {Event} e
   * @returns void|false
   */
  function handleClickedTrigger(e) {
    var $target = $(e.target);

    // Change target to parent if child element was clicked
    if (!$target.is($buttons) && $target.parent().is($buttons)) {
      $target = $target.parent();
    }

    if ($target.data('close-popper') !== undefined) {
      hidePopperAll($contents)
      return;
    }

    // if popover content was clicked or any of its children
    if ($contents.length > 0 && ($target.is($contents) || $.contains($contents.get(0), e.target))) {
      return;
    }

    if ($target.is($buttons)) {

      var index = $buttons.index($target.get(0));
      var instance = popperInstances[index];
      var $content = $target.parent().find('[data-content="popper"]');
      var popover = $content.get(0);


      if (!instance) return;

      togglePopper(instance, popover);
    } else {
      hidePopperAll($contents)
    }
  }

  function handleHoverTrigger(e) {
    var $target = $(e.target);

    if(!$target.data('hover')){
      $target = $target.closest('[data-hover="popper"]')
    }

    if(e.type === 'mouseleave'){
      hidePopperAll($contents)
      return;
    }

    if($target.length == 0) return;
    var index = $buttons.index(this);
    var instance = popperInstances[index];
    var $content = $target.parent().find('[data-content="popper"]');
    var popover = $content.get(0);
    // console.log('instance :>> ', instance);
    if (!instance) return;

    togglePopper(instance, popover);
  }


  function togglePopper(instance, tooltip) {
    if (tooltip.hasAttribute("data-show")) {
      hidePopper(instance, tooltip);
    } else {
      showPopper(instance, tooltip);
    }
  }

  //show and create popper
  function showPopper(instance, tooltip) {
    tooltip.setAttribute('data-show', '');

    instance.setOptions((options) => ({
      ...options,
      modifiers: [
        ...options.modifiers,
        { name: 'eventListeners', enabled: true },
      ],
    }));

    // Update its position
    instance.update();
  }

  //hide and destroy popper instance
  function hidePopper(instance, tooltip) {
    tooltip.removeAttribute('data-show');

    instance.setOptions((options) => ({
      ...options,
      modifiers: [
        ...options.modifiers,
        { name: 'eventListeners', enabled: false },
      ],
    }));
  }

  /**
   * hide and destroy all popper instances
   * @param {object} tooltips
   */
  function hidePopperAll(tooltips, destroy = false) {
    tooltips.each(function (index, tooltip) {
      tooltip.removeAttribute('data-show');
    })

    popperInstances.forEach(function (instance) {
      instance.setOptions((options) => ({
        ...options,
        modifiers: [
          ...options.modifiers,
          { name: 'eventListeners', enabled: false },
        ],
      }));

      if (destroy) {
        instance.destroy()
      }
    })
  }

  initialize();
});