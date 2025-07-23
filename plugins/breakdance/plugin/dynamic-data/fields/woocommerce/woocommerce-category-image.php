<?php

namespace Breakdance\DynamicData;

use function Breakdance\LoopBuilder\getCurrentTerm;

class WoocommerceCategoryImage extends ImageField
{

    /**
     * @inheritDoc
     */
    public function label()
    {
        return 'Product Category Image';
    }

    /**
     * @inheritDoc
     */
    public function category()
    {
        return 'Term';
    }

    /**
     * @inheritDoc
     */
    public function slug()
    {
        return 'woocommerce_category_image';
    }

    /**
     * @inheritDoc
     */
    public function handler($attributes): ImageData
    {
        /** 
         * @var \WP_Term|null 
         * @psalm-suppress UndefinedFunction
         */
        $term = getCurrentTerm(true);
        $termId = $term->term_id;

        $imageId = get_term_meta($termId, 'thumbnail_id', true);

        if (!$imageId) {
            return ImageData::emptyImage();
        }

        return ImageData::fromAttachmentId($imageId);
    }

    /**
     * @inheritDoc
     */
    function proOnly()
    {
        return true;
    }
}
