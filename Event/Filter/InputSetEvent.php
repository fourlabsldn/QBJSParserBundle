<?php

namespace FL\QBJSParserBundle\Event\Filter;

use FL\QBJSParserBundle\Model\Filter\FilterInput;
use Symfony\Component\EventDispatcher\Event;

class InputSetEvent extends Event
{
    const EVENT_NAME = 'qbjs_parser.filter_input_set';

    /**
     * @var FilterInput
     */
    protected $filterInput;

    /**
     * @var string
     */
    protected $filterId;

    /**
     * @var string
     */
    protected $builderId;

    /**
     * @param FilterInput $filterInput
     * @param string $filterId
     * @param string $builderId
     */
    public function __construct(FilterInput $filterInput, string $filterId, string $builderId)
    {
        $this->filterInput = $filterInput;
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