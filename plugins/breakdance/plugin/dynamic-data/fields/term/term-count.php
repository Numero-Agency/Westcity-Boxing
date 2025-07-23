<?php

namespace Breakdance\DynamicData;

use function Breakdance\LoopBuilder\getCurrentTerm;

class TermCount extends StringField
{
    /**
     * @inheritDoc
     */
    public function label()
    {
        return 'Term Count';
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
        return 'term_count';
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
        return StringData::fromString((string) $term->count);
    }

    /**
     * @inheritDoc
     */
    function proOnly()
    {
        return false;
    }
}
