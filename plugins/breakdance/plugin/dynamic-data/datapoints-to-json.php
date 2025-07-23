<?php

namespace Breakdance\DynamicData;

/**
 * @param string | null $postType
 * @return DynamicField[]
 */
function get_dynamic_fields_for_builder($postType)
{
    // Show all fields, ignoring the current post type.
    return DynamicDataController::getInstance()->getAllFields();
}
