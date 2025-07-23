<?php

namespace Breakdance\DynamicData;

class SearchQuery extends StringField
{

    /**
     * @inheritDoc
     */
    public function label()
    {
        return 'Search Query';
    }

    /**
     * @inheritDoc
     */
    public function category()
    {
        return 'URL & Query';
    }

    /**
     * @inheritDoc
     */
    public function slug()
    {
        return 'search_query';
    }

    public function returnTypes()
    {
        return ['query', 'string'];
    }

    public function handler($attributes): StringData
    {
        return StringData::fromString(get_search_query());
    }
}
