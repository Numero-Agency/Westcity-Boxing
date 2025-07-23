<?php

namespace Breakdance\DynamicData;

require_once __DIR__ . '/acf-repeater-field.php';

class AcfFlexibleContent extends AcfRepeaterField {
    /**
     * @inheritDoc
     */
    public function slug()
    {
        return 'acf_flexible_content_' . $this->field['key'];
    }

    /**
     * @return array{label: string, name: string}[]
     */
    public function getLayouts()
    {
        return $this->field['layouts'];
    }

    public function getLayoutNames()
    {
        return array_map(function ($layout) {
            return $layout['name'];
        }, $this->getLayouts());
    }

    public function hasSubFields($postId = false)
    {
        ray(parent::hasSubFields($postId));
        return parent::hasSubFields($postId);
    }

    public function returnTypes()
    {
        return ['flexible_content'];
    }
}
