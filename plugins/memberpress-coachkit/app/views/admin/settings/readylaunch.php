<?php if (!defined('ABSPATH')) { die('You are not allowed to call this page directly.'); } ?>
 <tr>
  <td>
    <label class="switch">
    <input x-model="coaching.enableTemplate" type="checkbox" id="<?php echo esc_attr($mepr_options->rl_enable_coaching_template_str); ?>" name="<?php echo esc_attr($mepr_options->rl_enable_coaching_template_str); ?>" value="1" class="mepr-template-enablers">
      <span class="slider round"></span>
    </label>
  </td>
  <td>
    <label for="<?php echo esc_attr($mepr_options->rl_enable_coaching_template_str); ?>"><?php esc_html_e('Coaching', 'memberpress-coachkit'); ?></label>
  </td>
  <td x-show="coaching.enableTemplate"></td>
</tr>