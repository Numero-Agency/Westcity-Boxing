<?php

namespace Breakdance\DynamicData;

use function Breakdance\LoopBuilder\getCurrentTerm;

class TermDescription extends StringField
{
    /**
     * @inheritDoc
     */
    public function label()
    {
        return 'Term Description';
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
        return 'term_description';
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
        return StringData::fromString(term_description($term));
    }

    /**
     * @inheritDoc
     */
    function proOnly()
    {
        return false;
    }
}
