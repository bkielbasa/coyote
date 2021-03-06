<?php

namespace Coyote\Services\Elasticsearch\Aggs\Job;

use Coyote\Services\Elasticsearch\Aggs;
use Coyote\Services\Elasticsearch\DslInterface;
use Coyote\Services\Elasticsearch\QueryBuilderInterface;

class Location extends Aggs\Nested implements DslInterface
{
    use Job;

    public function __construct()
    {
        parent::__construct('locations', 'city_original');
    }

    /**
     * @param QueryBuilderInterface $queryBuilder
     * @return array
     */
    public function apply(QueryBuilderInterface $queryBuilder)
    {
        return $this->buildGlobal(parent::apply($queryBuilder));
    }
}
