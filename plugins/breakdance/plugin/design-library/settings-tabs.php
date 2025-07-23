<?php

namespace Breakdance\DesignLibrary\Tab;

use function Breakdance\Setup\admin_notice;
use function Breakdance\Util\is_post_request;
use function Breakdance\Data\set_global_option;
use function Breakdance\DesignLibrary\getDesignLibraryUrl;
use function Breakdance\DesignLibrary\getPassword;
use function Breakdance\DesignLibrary\setPassword;
use function Breakdance\DesignLibrary\getRegisteredDesignSets;
use function Breakdance\DesignLibrary\setDesignSets;

add_action('breakdance_register_admin_settings_page_register_tabs', '\Breakdance\DesignLibrary\Tab\register');

function register()
{
    \Breakdance\Admin\SettingsPage\addTab('Design Library', 'design_library', '\Breakdance\DesignLibrary\Tab\tab', 1200);
}

function submit()
{
    $designLibraryEnabled = array_key_exists('is_copy_from_frontend_enabled', $_POST) ? 'yes' : false;
    $copyButtonEnabled = array_key_exists('is_copy_button_on_frontend_enabled', $_POST) ? 'yes' : false;
    $reliesOnGlobalSettings = array_key_exists('design_library_relies_on_global_settings', $_POST) ? 'yes' : false;
    $reliesOnDesignPresets = array_key_exists('design_library_relies_on_design_presets', $_POST) ? 'yes' : false;
    $design_library_full_site_import_enabled = array_key_exists('design_library_full_site_import_enabled', $_POST) ? 'yes' : false;

    /** @var string */
    $password = filter_input(INPUT_POST, 'design_library_password', FILTER_UNSAFE_RAW);

    /** @var string */
    $design_library_full_site_import_thumbnail_url = filter_input(INPUT_POST, 'design_library_full_site_import_thumbnail_url', FILTER_UNSAFE_RAW);

    /** @var string */
    $providers = filter_input(INPUT_POST, 'providers', FILTER_UNSAFE_RAW);

    set_global_option('is_copy_from_frontend_enabled', $designLibraryEnabled);
    set_global_option('is_copy_button_on_frontend_enabled', $copyButtonEnabled);
    set_global_option('design_library_relies_on_global_settings', $reliesOnGlobalSettings);
    set_global_option('design_library_relies_on_design_presets', $reliesOnDesignPresets);
    set_global_option('design_library_full_site_import_enabled', $design_library_full_site_import_enabled);
    set_global_option('design_library_full_site_import_thumbnail_url', $design_library_full_site_import_thumbnail_url);



    setDesignSets($providers);
    setPassword($password);
}

function tab()
{
    $nonce_action = 'breakdance_admin_design_library_tab';

    if (is_post_request() && check_admin_referer($nonce_action)) {
        submit();
    }

    /**
     * @var string
     */
    $designLibraryEnabled = \Breakdance\Data\get_global_option('is_copy_from_frontend_enabled');

    /**
     * @var string
     */
    $copyButtonEnabled = \Breakdance\Data\get_global_option('is_copy_button_on_frontend_enabled');

    /**
     * @var string
     */
    $reliesOnGlobalSettings = \Breakdance\Data\get_global_option('design_library_relies_on_global_settings');

    /**
     * @var string
     */
    $reliesOnDesignPresets = \Breakdance\Data\get_global_option('design_library_relies_on_design_presets');

    /**
     * @var string
     */
    $design_library_full_site_import_enabled = \Breakdance\Data\get_global_option('design_library_full_site_import_enabled');

    /**
     * @var string
     */
    $design_library_full_site_import_thumbnail_url = \Breakdance\Data\get_global_option('design_library_full_site_import_thumbnail_url');

    $providers = implode("\n", getRegisteredDesignSets());
?>

    <h2>Design Library</h2>

    <form action="" method="post">
        <?php wp_nonce_field($nonce_action); ?>
        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        Design Set
                    </th>
                    <td>
                        <fieldset>
                            <div>
                                <label for="is_copy_from_frontend_enabled">
                                    <input type="checkbox" <?php echo $designLibraryEnabled ? 'checked' : ''; ?> name="is_copy_from_frontend_enabled" value="yes" id="is_copy_from_frontend_enabled">
                                    Turn This Website Into a Design Set
                                </label>
                            </div>
                            <div>
                                <label for="is_copy_button_on_frontend_enabled">
                                    <input type="checkbox" <?php echo $copyButtonEnabled ? 'checked' : ''; ?> name="is_copy_button_on_frontend_enabled" value="yes" id="is_copy_button_on_frontend_enabled">
                                    Enable Copy From Frontend
                                </label>
                            </div>
                            <div>
                                <label for="design_library_relies_on_global_settings">
                                    <input type="checkbox" <?php echo $reliesOnGlobalSettings ? 'checked' : ''; ?> name="design_library_relies_on_global_settings" value="yes" id="design_library_relies_on_global_settings">
                                    This Design Set Relies On Global Settings
                                </label>
                            </div>
                            <div>
                                <label for="design_library_relies_on_design_presets">
                                    <input type="checkbox" <?php echo $reliesOnDesignPresets ? 'checked' : ''; ?> name="design_library_relies_on_design_presets" value="yes" id="design_library_relies_on_design_presets">
                                    This Design Set Relies On Design Presets
                                </label>
                            </div>

                            <div>
                                <label for="design_library_full_site_import_enabled">
                                    <input type="checkbox" <?php echo $design_library_full_site_import_enabled ? 'checked' : ''; ?> name="design_library_full_site_import_enabled" value="yes" id="design_library_full_site_import_enabled">
                                    Full Site Import Enabled
                                </label>
                            </div>

                            <div>
                                <label for="design_library_full_site_import_thumbnail_url">
                                    Thumbnail URL
                                </label>
                                <input type="text" name="design_library_full_site_import_thumbnail_url" value="<?php echo esc_attr($design_library_full_site_import_thumbnail_url); ?>" style="width: 400px">
                            </div>

                            <script type="text/javascript">
                                document.addEventListener('DOMContentLoaded', function() {
                                    var checkbox = document.querySelector('input[name="design_library_full_site_import_enabled"]');
                                    var thumbnailUrlDiv = document.querySelector('input[name="design_library_full_site_import_thumbnail_url"]').closest('div');

                                    function toggleThumbnailUrl() {
                                        if (checkbox.checked) {
                                            thumbnailUrlDiv.style.display = '';
                                        } else {
                                            thumbnailUrlDiv.style.display = 'none';
                                        }
                                    }

                                    // Initial toggle based on the checkbox state
                                    toggleThumbnailUrl();

                                    // Add event listener to the checkbox to toggle visibility on change
                                    checkbox.addEventListener('change', toggleThumbnailUrl);
                                });
                            </script>

                        </fieldset>

                        <hr />

                        <?php if ($designLibraryEnabled): ?>
                            <p class="description">
                                Shareable URL: <input class="breakdance-design-library-shareable-url" type="url" value="<?php echo getDesignLibraryUrl(); ?>" readonly style="width: 400px">
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        Password Protection
                    </th>
                    <td>
                        <input type="text" name="design_library_password" value="<?php echo esc_attr(getPassword()); ?>" placeholder="Enter a password">
                        <p class="description">
                            <small>
                                <em>Protect your Design Set with a password. (Optional) <br /> If the <b>?password=<?php echo esc_html(getPassword()); ?></b> param is not provided, users will be prompted to enter this password.</em>
                            </small>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="providers">Custom Design Sets</label></th>
                    <td>

                        <fieldset>
                            <p>
                                <textarea class="large-text code" cols="50" id="providers" name="providers" rows="10"><?php echo $providers; ?></textarea>
                            </p>
                        </fieldset>
                        <p class="description">
                            Add custom Design Sets to your Breakdance installation.
                            By default, Breakdance provides a list of official Design Sets, but you can use this field to add any custom design sets you want.
                        </p>
                        <p><strong>Insert one URL per line.</strong></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
        </p>

        <script>
            document.querySelector('.breakdance-design-library-shareable-url')
                ?.addEventListener('focus', (event) => {
                    event.currentTarget.select();
                });


            async function checkIfDesignSetsAreValid() {
                const formData = new FormData();
                formData.append("action", "breakdance_get_invalid_design_sets");

                const request = await fetch(window.ajaxurl, {
                    method: "POST",
                    credentials: "same-origin",
                    body: formData
                });

                const invalidUrls = await request.json();

                const notice = document.createElement('div');
                notice.classList.add('notice', 'notice-error');

                if (invalidUrls.length > 0) {
                    notice.innerHTML = `
                        <p>${invalidUrls.length === 1 ? 'The following URL is an invalid design set:' : 'The following URLs are invalid design sets'}</p>
                        <ul>
                            ${invalidUrls.map(url => `<li><strong>${url}</strong></li>`).join('')}
                        </ul>
                    `;

                    document.querySelector('.breakdance-admin__content').prepend(notice);
                }
            }

            checkIfDesignSetsAreValid();
        </script>
    </form>
<?php
}
