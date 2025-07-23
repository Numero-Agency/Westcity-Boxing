<?php

namespace Breakdance\DynamicData;

use function Breakdance\LoopBuilder\getCurrentTerm;

class TermId extends StringField
{
    /**
     * @inheritDoc
     */
    public function label()
    {
        return 'Term ID';
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
        return 'term_id';
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
        return StringData::fromString((string) $term->term_id);
    }

    /**
     * @inheritDoc
     */
    function proOnly()
    {
        return false;
    }
}
