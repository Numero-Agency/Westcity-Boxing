<?php

namespace Breakdance\DynamicData;

class PHPField extends Field
{

    /**
     * @inheritDoc
     */
    public function label()
    {
        return 'PHP';
    }

    /**
     * @inheritDoc
     */
    public function category()
    {
        return 'Utility';
    }

    /**
     * @inheritDoc
     */
    public function slug()
    {
        return 'phpreturn';
    }

    /**
     * @inheritDoc
     */
    public function controls()
    {
        return [
            \Breakdance\Elements\control(
                'code',
                'Code',
                [
                    'placeholder' => 'return $value;',
                    'codeOptions' => ['language' => 'x-php', 'autofillOnEmpty' => 'return $value;PLACECURSORHERE'],
                    'type' => "code",
                    'layout' => 'vertical'
                ]
            ),
        ];
    }

    /**
     * @inheritDoc
     */
    public function returnTypes()
    {
        return ['image_url', 'string'];
    }

    /**
     * @inheritDoc
     */
    public function handler($attributes): FieldData
    {
        $code = $attributes['code'] ?? "return '';";

        try {
            $result = eval($code);
        } catch (\ParseError $e) {
            ob_start();
            echo 'An error occurred: <br />';
            echo 'Caught exception: ' . $e->getMessage() . "\n";
            echo 'Line: ';
            echo $e->getLine();
            echo "<br />";
            $result = ob_get_clean();
        }

        return StringData::fromString($result);
    }
}
