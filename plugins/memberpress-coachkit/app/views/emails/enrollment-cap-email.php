<div id="header" style="width: 680px; padding: 0px; margin: 0 auto; text-align: left;">
  <h1 style="font-size: 30px; margin-bottom: 15px;"><?php echo esc_html_x( 'Enrollment Limit Notice', 'ui', 'memberpress-coachkit' ); ?></h1>
  <h2 style="margin-top: 0; color: #999; font-weight: normal;"><?php echo '{$program_name}'; ?></h2>
</div>
<div id="body" style="width: 600px; background: white; padding: 40px; margin: 0 auto; text-align: left;">

  <p><?php echo esc_html_x( 'This is to alert you that the enrollment limit for the following program is nearing its maximum capacity:', 'ui', 'memberpress-coachkit' ); ?></p>

  <table style="clear: both;" class="enrollment-details">
    <tr>
      <th style="text-align: left;"><?php echo esc_html_x( 'Program:', 'ui', 'memberpress-coachkit' ); ?></th>
      <td>{$program_name}</td>
    </tr>
    <tr>
      <th style="text-align: left;"><?php echo esc_html_x( 'Membership:', 'ui', 'memberpress-coachkit' ); ?></th>
      <td>{$membership_name}</td>
    </tr>
    <tr>
      <th style="text-align: left;"><?php echo esc_html_x( 'Available Seats:', 'ui', 'memberpress-coachkit' ); ?></th>
      <td>{$remaining_enrollments}</td>
    </tr>
  </table>

  <p><?php echo wp_kses_post( _x( 'Once the limit is reached, users <b>will not</b> be able to register for the {$membership_name} membership. To ensure uninterrupted access, please consider adjusting the enrollment limit.', 'ui', 'memberpress-coachkit' ) ); ?></p>

  <p><strong><a href="{$edit_program_url}"><?php echo esc_html_x( 'Edit Enrollment Limit', 'ui', 'memberpress-coachkit' ); ?></a></strong></p>

  <p><?php echo esc_html_x( "If the current enrollment limit aligns with your intentions, there's no need to take any action", 'ui', 'memberpress-coachkit' ); ?></p>

</div>
