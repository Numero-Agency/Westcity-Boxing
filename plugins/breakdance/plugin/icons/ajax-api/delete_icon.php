<?php

namespace Breakdance\Icons\AjaxApi;

use function Breakdance\Icons\delete_icon;

\Breakdance\AJAX\register_handler('breakdance_delete_icon', function (int $id) {
    delete_icon($id);
}, 'edit', true, [
    'args' => [
        'id' => FILTER_VALIDATE_INT
    ],
]);
