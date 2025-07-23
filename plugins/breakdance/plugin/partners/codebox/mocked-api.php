<?php

// @psalm-ignore-file

namespace BreakdanceWPCodeBox;

// API for WPCodeBox To build
/**
 * @param string $code
 * @return int
 */
function saveSnippetToCodeBox($code)
{

    $wpcbApi = new \Wpcb2\Api\Api();

    try {
        $snippetId = $wpcbApi->createSnippet(
            [
                'code' => $code,
                'codeType' => ['value' => 'php'],
                'minify' => false,
                'title' => 'Breakbreak Snippet ' . date('Y-m-d H:i:s'),
                'priority' => 10,
                'conditions' => [],
                'description' => '',
                'location' => '',
                'runType' => ['value' => 'never'],
            ]
        );
        return $snippetId;
    } catch (\Exception $e) {
        return 0;
    }
}

/**
 * @param int $id
 */
function runSnippet($id)
{
    $wpcbApi = new \Wpcb2\Api\Api();
    $wpcbApi->runSnippet($id);
}
