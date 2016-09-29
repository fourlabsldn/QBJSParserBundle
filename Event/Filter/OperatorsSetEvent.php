<?php

namespace FL\QBJSParserBundle\Event\Filter;

use FL\QBJSParserBundle\Model\Filter\FilterOperators;
use Symfony\Component\EventDispatcher\Event;

class OperatorsSetEvent extends Event
{
    const EVENT_NAME = 'qbjs_parser.filter_operators_set';

    /**
     * @var FilterOperators
     */
    protected $filterOperators;

    /**
     * @var string
     */
    protected $filterId;

    /**
     * @var string
     */
    protected $builderId;

    /**
     * @param FilterOperators $filterOperators
     * @param string $filterId
     * @param string $builderId
     */
    public function __construct(FilterOperators $filterOperators, string $filterId, string $builderId)
    {
        $this->filterOperators = $filterOperators;
        $this->filterId = $filterId;
        $this->builderId = $builderId;
    }

    /**
     * @return FilterOperators
     */
    public function getFilterOperators(): FilterOperators
    {
        return $this->filterOperators;
    }

    /**
     * @return string
     */
    public function getFilterId(): string
    {
        return $this->filterId;
    }

    /**
     * @return string
     */
    public function getBuilderId(): string
    {
        return $this->builderId;
    }
}