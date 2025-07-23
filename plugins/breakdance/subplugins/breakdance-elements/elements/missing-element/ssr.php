<?php

if (!\Breakdance\Permissions\hasMinimumPermission('edit')) {
    echo '<!-- This element is missing. Please open the page in Breakdance and check the browser console for details. -->';
} else {
?>
    <div class="bde-missing-element__message">
        <p>This element is missing. Please open the page in Breakdance and check the browser console for details.</p>
        <p>Have questions? We’re here to help. <a href="https://breakdance.com/support/" target="_blank">Click here</a> to contact support.</p>
    </div>
<?php
}
