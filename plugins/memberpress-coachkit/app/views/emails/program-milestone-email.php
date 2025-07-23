<?php if ( ! defined( 'ABSPATH' ) ) {
  die( 'You are not allowed to call this page directly.' );
} ?>
<div id="header" style="width: 680px; padding: 0px; margin: 0 auto 14px; text-align: left;">
  <h1 style="font-size: 24px; margin-bottom:4px;">{$checkin_question}</h1>
  <h2 style="margin-top: 0; color: #999; font-weight: normal;"><?php echo '{$program_title}'; ?></h2>
</div>
<div id="body" style="width: 600px; background: white; padding: 40px; margin: 0 auto; text-align: left;">
  <div id="receipt">
    <div class="section" style="display: block; margin-bottom: 24px;"><?php echo esc_html_x( 'This is a friendly reminder to complete your check-in for the following milestone and program:', 'ui', 'memberpress-coachkit' ); ?></div>
    <div class="section" style="display: block; margin-bottom: 24px;">
      <table style="clear: both;" class="transaction">
        <tr>
          <th style="text-align: left;"><?php echo esc_html_x( 'Milestone:', 'ui', 'memberpress-coachkit' ); ?></th>
          <td>{$title}</td>
        </tr>
        <tr>
          <th style="text-align: left;"><?php echo esc_html_x( 'Program:', 'ui', 'memberpress-coachkit' ); ?></th>
          <td>{$program_title}</td>
        </tr>
      </table>
    </div>
    <div class="section" style="display: block; margin-bottom: 24px;"><?php echo esc_html_x( 'Please take a moment to provide an update on your milestone by clicking on the link below:', 'ui', 'memberpress-coachkit' ); ?><br/><a href="{$checkin_complete_url}"><?php echo esc_html_x( 'Complete Check-in', 'ui', 'memberpress-coachkit' ); ?></a></div>
    <div class="section" style="display: block; margin-bottom: 24px;">
      <?php echo esc_html_x( 'Cheers!', 'ui', 'memberpress-coachkit' ); ?> <br/>
      <?php echo esc_html_x( 'The {$blog_name} Team', 'ui', 'memberpress-coachkit' ); ?>
    </div>
  </div>
</div>
