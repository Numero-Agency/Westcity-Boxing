<?php if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
} ?>
<div id="header" style="width: 680px; padding: 0px; margin: 0 auto 14px; text-align: left;">
  <h1 style="font-size: 24px; margin-bottom:4px;"><?php echo esc_html_x( 'Program Started Notice', 'ui', 'memberpress-coachkit' ); ?></h1>
  <h2 style="margin-top: 0; color: #999; font-weight: normal;"><?php echo '{$program_name}'; ?></h2>
</div>
<div id="body" style="width: 600px; background: white; padding: 40px; margin: 0 auto; text-align: left;">
  <div id="receipt">
    <div class="section" style="display: block; margin-bottom: 24px;"><?php echo esc_html_x( 'This email is to inform you that {$enrollee} has successfully completed the "{$program_name}" program', 'ui', 'memberpress-coachkit' ); ?></div>
  </div>
</div>
