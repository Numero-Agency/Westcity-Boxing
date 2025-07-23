<?php

namespace Breakdance\DynamicData;

use function Breakdance\LoopBuilder\getCurrentTerm;

class WooCommerceCategoryCount extends StringField
{
    /**
     * @inheritDoc
     */
    public function label()
    {
        return 'Product Category Count';
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
        return 'woocommerce_category_count';
    }

    /**
     * @inheritDoc
     */
    public function handler($attributes): StringData
    {
        /** 
         * @var \WP_Term|null 
         * @psalm-suppress UndefinedFunction
         */
        $term = getCurrentTerm(true);
        $termId = $term->term_id;

        $value = get_term_meta($termId, 'product_count_product_cat', true);

        if (!$value) {
            return StringData::emptyString();
        }

        return StringData::fromString($value);
    }

    /**
     * @inheritDoc
     */
    function proOnly()
    {
        return false;
    }
}
