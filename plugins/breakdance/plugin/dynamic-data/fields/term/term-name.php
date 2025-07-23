<?php

namespace Breakdance\DynamicData;

use function Breakdance\LoopBuilder\getCurrentTerm;

class TermName extends StringField
{
    /**
     * @inheritDoc
     */
    public function label()
    {
        return 'Term Name';
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
        return 'term_name';
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
        return StringData::fromString($term->name);
    }

    /**
     * @inheritDoc
     */
    function proOnly()
    {
        return false;
    }
}
