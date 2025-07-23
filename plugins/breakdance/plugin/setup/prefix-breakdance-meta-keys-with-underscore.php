<?php

/**
 * @psalm-ignore-file
 */

namespace Breakdance\Setup;

function updateMetaKey(string $old_key, string $new_key)
{
    /** @var \wpdb $wpdb */
    global $wpdb;

    /**
     * @psalm-suppress MixedPropertyFetch
     * @psalm-suppress MixedMethodCall
     */
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->postmeta}
            SET meta_key = %s
            WHERE meta_key = %s",
            $new_key,
            $old_key
        )
    );
}

function prefixBreakdanceMetaKeysWithUnderscore()
{
    updateMetaKey('breakdance_data', '_breakdance_data');
    updateMetaKey('breakdance_template_settings', '_breakdance_template_settings');
    updateMetaKey('breakdance_dependency_cache', '_breakdance_dependency_cache');
    updateMetaKey('breakdance_css_file_paths_cache', '_breakdance_css_file_paths_cache');
    updateMetaKey('template_last_previewed_item', '_breakdance_template_last_previewed_item');

    // Take the opportunity to fix the form settings key as well
    updateMetaKey('_breakdance_settings', '_breakdance_form_settings');
}

function undoPrefixBreakdanceMetaKeysWithUnderscore()
{
    // Ignore breakdance_data for 2.0.0 because when you downgrade from 2.0.0 to 1.7.2,
    // the tool will target breakdance_data specifically.
    // updateMetaKey('_breakdance_data', 'breakdance_data');
    updateMetaKey('_breakdance_template_settings', 'breakdance_template_settings');
    updateMetaKey('_breakdance_dependency_cache', 'breakdance_dependency_cache');
    updateMetaKey('_breakdance_css_file_paths_cache', 'breakdance_css_file_paths_cache');
    updateMetaKey('_breakdance_template_last_previewed_item', 'template_last_previewed_item');
    updateMetaKey('_breakdance_form_settings', '_breakdance_settings');
}

/**
 * @return bool
 */
function isDatabaseUpdateRequired()
{
    /** @var \wpdb $wpdb */
    global $wpdb;

    $old_keys = [
        'breakdance_data',
        'breakdance_template_settings',
        'breakdance_dependency_cache',
        'breakdance_css_file_paths_cache',
        'template_last_previewed_item',
        '_breakdance_settings'
    ];

    $placeholders = implode(',', array_fill(0, count($old_keys), '%s'));

    /**
     * @psalm-suppress MixedPropertyFetch
     * @psalm-suppress MixedMethodCall
     */
    $result = $wpdb->get_var(
        /** @psalm-suppress MixedPropertyFetch */
        $wpdb->prepare(
            "SELECT COUNT(*)
                FROM {$wpdb->postmeta}
                WHERE meta_key IN ($placeholders)",
            $old_keys
        )
    );

    return $result > 0;
}

function prefixAdminNotice()
{
    if (!isDatabaseUpdateRequired()) return;

    $nonce = $_GET['_nonce'] ?? '';

    if (isset($_GET['breakdance_database_update']) && wp_verify_nonce( $nonce, 'breakdance_database_update' )) {
        if (!current_user_can('manage_options'))  {
            wp_die( __('You do not have sufficient permissions to access this page.') );
        }

        prefixBreakdanceMetaKeysWithUnderscore();

        if (!isDatabaseUpdateRequired()) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>Breakdance database has been updated successfully!</p>
            </div>
            <?php
            return;
        }
    }

    ?>
    <div class="notice" id="message">
        <h2>Breakdance Database Update Required</h2>
        <p>Breakdance has been updated! The next step is to run the database updater to ensure that all data is migrated correctly.</p>
        <p>
            <a class="button button-primary" href="<?php echo admin_url('?breakdance_database_update=true&_nonce=' . wp_create_nonce('breakdance_database_update')); ?>">Update database now</a>
        </p>
    </div>
    <?php
}
add_action( 'admin_notices', '\Breakdance\Setup\prefixAdminNotice');
