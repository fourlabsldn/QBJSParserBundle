<?php

namespace FL\QBJSParserBundle\Event\Filter;

use FL\QBJSParserBundle\Model\Filter\FilterInput;
use FL\QBJSParserBundle\Model\Filter\FilterOperators;
use FL\QBJSParserBundle\Model\Filter\FilterValueCollection;
use Symfony\Component\EventDispatcher\Event;

class FilterSetEvent extends Event
{
    const EVENT_NAME = 'fl_qbjs_parser.filter_set_event';

    /**
     * @var FilterInput
     */
    protected $filterInput;

    /**
     * @var FilterOperators
     */
    protected $filterOperators;

    /**
     * @var FilterValueCollection
     */
    protected $filterValueCollection;

    /**
     * @var string
     */
    protected $filterId;

    /**
     * @var string
     */
    protected $builderId;

    /**
     * @param FilterInput           $filterInput
     * @param FilterOperators       $filterOperators
     * @param FilterValueCollection $filterValueCollection
     * @param string                $filterId
     * @param string                $builderId
     */
    public function __construct(FilterInput $filterInput, FilterOperators $filterOperators, FilterValueCollection $filterValueCollection, string $filterId, string $builderId)
    {
        $this->filterInput = $filterInput;
        $this->filterOperators = $filterOperators;
        $this->filterValueCollection = $filterValueCollection;
        $this->filterId = $filterId;
        $this->builderId = $builderId;
    }

    /**
     * @return FilterInput
     */
    public function getFilterInput(): FilterInput
    {
        return $this->filterInput;
    }

    /**
     * @return FilterOperators
     */
    public function getFilterOperators(): FilterOperators
    {
        return $this->filterOperators;
    }

    /**
     * @return FilterValueCollection
     */
    public function getFilterValueCollection(): FilterValueCollection
    {
        return $this->filterValueCollection;
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
