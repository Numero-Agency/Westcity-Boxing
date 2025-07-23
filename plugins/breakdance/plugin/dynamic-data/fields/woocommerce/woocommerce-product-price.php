<?php

namespace Breakdance\DynamicData;

class WoocommerceProductPrice extends StringField {

    /**
     * @inheritDoc
     */
    public function label()
    {
        return 'Product Price';
    }

    /**
     * @inheritDoc
     */
    public function category()
    {
        return 'WooCommerce';
    }

    /**
     * @inheritDoc
     */
    public function slug()
    {
        return 'product_price';
    }

    /**
     * @inheritDoc
     */
    public function controls()
    {
        return [
            \Breakdance\Elements\control('product', 'Product', [
                    'type' => 'post_chooser',
                    'layout' => 'vertical',
                    'postChooserOptions' => [
                        'multiple' => false,
                        'showThumbnails' => false,
                        'postType' => 'Product'
                    ]
                ]
            ),
            \Breakdance\Elements\control('price_type', 'Price Type', [
                'type' => 'dropdown',
                'layout' => 'vertical',
                'items' => array_merge([
                    ['text' => 'Regular Price', 'value' => 'regular'],
                    ['text' => 'Sale Price', 'value' => 'sale'],
                ])
            ]),
        ];
    }

    public function defaultAttributes()
    {
        return [
            'price_type' => 'regular'
        ];
    }

    /**
     * @inheritDoc
     */
    public function handler($attributes): StringData
    {
        global $post;
        $productId = $post->ID ?? null;
        if (!empty($attributes['product'])) {
            $productId = $attributes['product'];
        }
        $product = wc_get_product($productId);

        if (!$product) {
            return StringData::emptyString();
        }

        return StringData::fromString($product->get_price_html());
    }
}
