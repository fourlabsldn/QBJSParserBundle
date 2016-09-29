<?php

namespace FL\QBJSParserBundle\Event\Filter;

use FL\QBJSParserBundle\Model\Filter\FilterValueCollection;
use Symfony\Component\EventDispatcher\Event;

class ValuesSetEvent extends Event
{
    const EVENT_NAME = 'qbjs_parser.filter_value_set';

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
     * @param FilterValueCollection $filterValueCollection
     * @param string $filterId
     * @param string $builderId
     */
    public function __construct(FilterValueCollection $filterValueCollection, string $filterId, string $builderId)
    {
        $this->filterValueCollection = $filterValueCollection;
        $this->filterId = $filterId;
        $this->builderId = $builderId;
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