<section class="<?php echo $in_progress ? 'mpch-in-progress' : '' ?>">
  <nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary menu">
    <a href="#0" class="nav-tab nav-tab-active" id="milestones"><?php echo esc_html__('Milestones', 'memberpress-coachkit'); ?></a>
    <a href="#0" id="habits" class="nav-tab"><?php echo esc_html__('Habits', 'memberpress-coachkit'); ?></a>
  </nav>

  <div id="program-tab-wrapper">
    <!-- Milestones -->
    <div class="program-tab-content program-milestones">
      <?php
      foreach ($milestones as $milestone) {
        echo $milestone; // phpcs:ignore WordPress.Security.EscapeOutput
      }
      ?>
    </div>

    <!-- Habits -->
    <div class="program-tab-content program-habits" style="display: none;">
      <?php
      foreach ($habits as $habit) {
        echo $habit; // phpcs:ignore WordPress.Security.EscapeOutput
      }
      ?>
      <!-- <button class="mpch-metabox__more mpch-metabox__button" type="button" data-action="add-habit">
      <i class="mp-icon mp-icon-plus-circled mp-32"></i>
    </button> -->
      <button class="mpch-metabox__button --secondary" type="button" data-action="add-habit">
        <span><?php echo esc_html_x('New Habit', 'ui', 'memberpress-coachkit'); ?></span>
      </button>
    </div>

    <?php
    wp_nonce_field(memberpress\coachkit\models\Program::NONCE_STR . \wp_salt(), memberpress\coachkit\models\Program::NONCE_STR);
    ?>

  </div>
</section>