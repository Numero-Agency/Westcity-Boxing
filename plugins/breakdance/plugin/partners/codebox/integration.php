<?php

namespace Breakdance\Partners\CodeBox;

add_action(
    'breakdance_loaded',
    function () {
        \Breakdance\AJAX\register_handler(
            'breakdance_codebox_send',
            '\Breakdance\Partners\CodeBox\send_to_codebox',
            'edit',
            true,
            [
                'args' => [
                    'code' => FILTER_UNSAFE_RAW,
                ]
            ]
        );
    }
);

/**
 * @param string $code
 * @return array
 */
function send_to_codebox($code)
{
    if (!isCodeBoxInstalled()) {
        return [
            'status' => 'codebox_not_installed',
        ];
    }
    /**
     * @psalm-suppress UndefinedFunction
     * @var int
     */
    $snippetId = \BreakdanceWPCodeBox\saveSnippetToCodeBox($code);
    return [
        'status' => 'success',
        'data' => [
            'snippetId' => $snippetId
        ]
    ];
}

/**
 * @return boolean
 */
function isCodeBoxInstalled()
{
    return class_exists('\Wpcb2\Api\Api');
}
