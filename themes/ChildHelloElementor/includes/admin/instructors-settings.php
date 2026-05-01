<?php
/**
 * Instructors settings — admin-managed list of class instructors.
 *
 * Adds Settings → Instructors in WP Admin. Stores names in a single
 * wp_options row (wcb_instructors) as an array of strings.
 *
 * Existing session_log posts store the instructor field as a plain
 * string via ACF; that data is not affected by this file. If a name
 * is later removed from this list, historical sessions still display
 * the saved string — only the dropdown for new sessions changes.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Default list — used when the option has never been saved, so the form keeps
// working identically for existing installs until an admin edits the list.
function wcb_default_instructors() {
    return [
        'Xarisma Paga',
        'Dion Tafa',
        'Hala Houma',
        'Jasmin bunton',
        'Zarah Kumar',
        'Sebastian Grey',
        'Matthew Grey',
        'Shamil Kumar',
    ];
}

/**
 * Returns the current instructor list. Always returns an array of non-empty
 * trimmed strings. Falls back to defaults when the option is missing/empty.
 */
function wcb_get_instructors() {
    $stored = get_option('wcb_instructors', null);

    if ($stored === null) {
        return wcb_default_instructors();
    }

    if (!is_array($stored)) {
        return wcb_default_instructors();
    }

    $clean = [];
    foreach ($stored as $name) {
        $name = is_string($name) ? trim($name) : '';
        if ($name !== '') {
            $clean[] = $name;
        }
    }

    return !empty($clean) ? $clean : wcb_default_instructors();
}

add_action('admin_menu', 'wcb_register_instructors_settings_page');
function wcb_register_instructors_settings_page() {
    add_options_page(
        'Instructors',
        'Instructors',
        'manage_options',
        'wcb-instructors',
        'wcb_render_instructors_settings_page'
    );
}

add_action('admin_init', 'wcb_register_instructors_setting');
function wcb_register_instructors_setting() {
    register_setting(
        'wcb_instructors_group',
        'wcb_instructors',
        [
            'type' => 'array',
            'sanitize_callback' => 'wcb_sanitize_instructors_option',
            'default' => wcb_default_instructors(),
        ]
    );
}

/**
 * Accepts the textarea POST value (one name per line) and returns a clean
 * array of unique non-empty strings.
 */
function wcb_sanitize_instructors_option($value) {
    if (is_array($value)) {
        $lines = $value;
    } else {
        $value = (string) $value;
        $lines = preg_split('/\r\n|\r|\n/', $value);
    }

    $clean = [];
    $seen = [];
    foreach ($lines as $line) {
        $name = sanitize_text_field((string) $line);
        $name = trim($name);
        if ($name === '') {
            continue;
        }
        $key = mb_strtolower($name);
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;
        $clean[] = $name;
    }

    return $clean;
}

function wcb_render_instructors_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $instructors = wcb_get_instructors();
    $textarea_value = implode("\n", $instructors);
    ?>
    <div class="wrap">
        <h1>Instructors</h1>
        <p>Manage the list of instructors that appears in the Class Session Log dropdown. One name per line.</p>
        <p><em>Removing a name here only affects the dropdown for new sessions. Sessions already saved with that instructor will continue to display the saved name.</em></p>

        <form method="post" action="options.php">
            <?php settings_fields('wcb_instructors_group'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="wcb_instructors_textarea">Instructor names</label>
                    </th>
                    <td>
                        <textarea
                            id="wcb_instructors_textarea"
                            name="wcb_instructors"
                            rows="12"
                            cols="40"
                            class="large-text code"
                            placeholder="One name per line"><?php echo esc_textarea($textarea_value); ?></textarea>
                        <p class="description">Enter one instructor name per line. Order is preserved. Duplicate names (case-insensitive) are removed automatically.</p>
                    </td>
                </tr>
            </table>

            <?php submit_button('Save Instructors'); ?>
        </form>
    </div>
    <?php
}
