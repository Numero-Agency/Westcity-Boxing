<?php

namespace Breakdance\SetupWizard;

use Breakdance\Licensing\LicenseKeyManager;
use function Breakdance\Data\set_global_option;
use function Breakdance\Util\is_post_request;

add_action('breakdance_admin_menu', function () {
    add_submenu_page('options.php', 'Setup Wizard', 'Setup Wizard', 'manage_options', 'breakdance_setup_wizard', "Breakdance\SetupWizard\display_setup_wizard");
});

function display_setup_wizard()
{
    $nonce_action = 'breakdance_admin_setup-wizard';
    /**
     * @var string|null $key_error
     */
    $key_error = null;

    $disable_theme = true;
    $analyticswp = false;
    $disable_bloat = false;
    $enable_tracking = true;

    if (is_post_request() && check_admin_referer($nonce_action)) {
        $disable_theme = (bool) filter_input(INPUT_POST, 'disable_theme', FILTER_VALIDATE_BOOLEAN);
        $disable_bloat = (bool) filter_input(INPUT_POST, 'disable_bloat', FILTER_VALIDATE_BOOLEAN);
        $analyticswp = (bool) filter_input(INPUT_POST, 'analyticswp', FILTER_VALIDATE_BOOLEAN);
        $enable_tracking = (bool) filter_input(INPUT_POST, 'enable_tracking', FILTER_VALIDATE_BOOLEAN);

        /**
         * @var mixed|null|false $key
         */
        $key = filter_input(INPUT_POST, 'key', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if ($disable_theme) {
            set_global_option('is_theme_disabled', 'yes');

            if ($disable_bloat) {
                set_global_option('breakdance_settings_bloat_eliminator', [
                    'gutenberg-blocks-css',
                    'rsd-links',
                    'wlw-link',
                    'rest-api',
                    'shortlink',
                    'rel-links',
                    'wp-generator',
                    'feed-links',
                    'xml-rpc',
                    'wp-emoji',
                    'wp-oembed',
                    'wp-dashicons',
                ]);
            } else {
                set_global_option('breakdance_settings_bloat_eliminator', []);
            }
        } else {
            $disable_bloat = false;
            set_global_option('is_theme_disabled', 'no');
            set_global_option('breakdance_settings_bloat_eliminator', []);
        }

        if ($key !== null && $key !== false) {
            $trimmed_key = trim((string) $key);

            LicenseKeyManager::getInstance()->changeLicenseKey($trimmed_key === '' ? null : $trimmed_key);
        }

        if ($analyticswp) {
            /** @psalm-suppress UndefinedFunction  */
            \Breakdance\Partners\AnalyticsWP\installAnalyticsWPPlugin();
        }

        // Save the tracking preference
        set_global_option('enable_tracking', $enable_tracking ? 'yes' : 'no');

        if ($key_error === null) {
            // wp_redirect doesn't work here because headers are already sent
            print('<script>window.location.href="admin.php?page=breakdance"</script>');
        }
    }
?>
    <style>
        .form-table {
            margin-top: 20px;
            max-width: 1100px;
        }

        .form-table th,
        .form-table td {
            border: 1px solid #d5d5d5;
            padding: 20px;
        }

        table.form-table {
            background-color: white;
            border-collapse: collapse;
        }

        .valign-th-middle {
            vertical-align: middle !important;
        }

        .padded-notice {
            margin-top: 20px !important;
            margin-bottom: 20px !important;
        }
    </style>
    <script>
        window.breakdanceSetupWizardShowBloatRow = (e) => {
            // const row = window.document.getElementById('disable_bloat_row');
            // if (row) {
            //     row.style.display = 'table-row';
            // }
        }
        window.breakdanceSetupWizardHideBloatRow = (e) => {
            // const row = window.document.getElementById('disable_bloat_row');
            // if (row) {
            //     row.style.display = 'none';
            // }
        }
    </script>
    <div class="wrap">
        <h1>Breakdance Setup Wizard</h1>

        <form action="" method="post">
            <?php
            wp_nonce_field($nonce_action); ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">Theme</th>
                        <td>

                            <style>
                                .disable-theme-recommended {
                                    font-size: 0.65em;
                                    display: inline-block;
                                    padding: 5px;
                                    background-color: #d2f2b8;
                                    color: #31610a;
                                    line-height: 1;
                                    font-weight: 500;
                                    border-radius: 3px;
                                    position: relative;
                                    top: -2px;
                                    text-transform: uppercase;
                                }
                            </style>

                            <fieldset>
                                <label for="disable_theme__yes"><input type="radio" id="disable_theme__yes" name="disable_theme" value="true" onchange="breakdanceSetupWizardShowBloatRow()" <?= $disable_theme ? 'checked' : '' ?> />

                                    Disable Theme <span class='disable-theme-recommended'>recommended</span>
                                    <p class="description">Design every part of your site with Breakdance. Disabling your theme gives you the best performance and maximum flexibility.</p>

                                </label><br />
                                <label for="disable_theme__no"><input type="radio" id="disable_theme__no" name="disable_theme" onchange="breakdanceSetupWizardHideBloatRow()" <?= $disable_theme ? '' : 'checked' ?> value="false" />

                                    Keep Theme
                                    <p class="description">The design of your existing site won't be affected. Your theme's styles may affect the design of Breakdance elements.</p>

                                </label>
                            </fieldset>

                            <br />
                            <p class="description">You can change this at any time from
                                <code>Breakdance
                                    &rarr; Settings &rarr; Theme</code>
                            </p>
                        </td>
                    </tr>
                    <tr id="disable_bloat_row" style="<?= $disable_theme ? 'display: none' : 'display: none' ?>">
                        <th scope="row">Performance</th>
                        <td>
                            <fieldset>
                                <label for="disable_bloat__yes"><input type="radio" id="disable_bloat__yes" name="disable_bloat" value="true" <?= $disable_bloat ? 'checked' : '' ?> />Clean Common Bloat - dashicons for logged out users, disable Gutenberg CSS, and disable WP Emoji JS.</label><br />
                                <label for="disable_bloat__no"><input type="radio" id="disable_bloat__no" name="disable_bloat" value="false" <?= $disable_bloat ? '' : 'checked' ?> />No</label><br />
                            </fieldset>

                            <p class='description'>You can change this at any time from
                                Breakdance
                                &rarr; Settings &rarr; Performance.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">License Key</th>
                        <td>
                            <fieldset>
                                <label for="key">License Key (optional)</label><br />
                                <input type="text" id="key" style="width: 360px;" value="<?= strval($key ?? '') ?>" name="key" />
                                <p class='description'>If you purchased Breakdance, enter your license key here. You can find your license key at <a href="https://breakdance.com/portal" target="_blank">https://breakdance.com/portal</a>.</p>
                            </fieldset>

                            <?php if ($key_error !== null): ?>
                                <div class="notice notice-error inline padded-notice">
                                    <p>Failed to activate the key: <?= $key_error; ?></p>
                                </div>
                            <?php endif; ?>

                        </td>
                    </tr>


                    <tr>
                        <th scope="row">AnalyticsWP Plugin Integration
                        </th>
                        <td>
                            <fieldset>
                                <label for="analyticswp__yes"><input type="radio" id="analyticswp__yes" name="analyticswp" value="true" <?= $analyticswp ? 'checked' : '' ?> />

                                    Install AnalyticsWP Plugin
                                    <p class="description">View the complete customer journey for visitors that submit Breakdance forms, or use the <em>AnalyticsWP Event</em> element to track user activity on a page.</p>
                                    <p class="description">
                                        Read the <a href='https://breakdance.com/documentation/integrations/analyticswp/' target='_blank'>Breakdance AnalyticsWP integration documentation</a> or visit the
                                        <a href='https://analyticswp.com/?utm_campaign=breakdance_setup_wizard' target='_blank'>AnalyticsWP Plugin Homepage</a> for more information.
                                    </p>
                                </label><br />
                                <label for="analyticswp__no"><input type="radio" id="analyticswp__no" name="analyticswp" <?= $analyticswp ? '' : 'checked' ?> value="false" />

                                    Don't Install
                                    <p class="description">AnalyticsWP will not be automatically installed.</p>

                                </label><br />
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Improve Breakdance</th>
                        <td>
                            <fieldset>
                                <label for="enable_tracking">
                                    <input type="checkbox" id="enable_tracking" name="enable_tracking" value="true" <?= $enable_tracking ? 'checked' : '' ?> />
                                    Yes, I want to help improve Breakdance
                                </label>
                                <p class="description"> By allowing us to <a href='https://breakdance.com/documentation/settings/privacy/' target='_blank'>collect usage data</a>, you help us make Breakdance better for everyone.</p>

                            </fieldset>

                            <p class="description">You can change this at any time from <code>Breakdance &rarr; Settings &rarr; Privacy</code></p>
                        </td>
                    </tr>

                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Finish Setup" />
            </p>
        </form>
    </div>
<?php
}

?>