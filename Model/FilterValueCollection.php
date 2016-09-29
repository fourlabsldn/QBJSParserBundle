<?php

namespace FL\QBJSParserBundle\Model;

class FilterValueCollection
{
    /**
     * @var \SplObjectStorage
     */
    private $filterValues;

    public function __construct()
    {
        $this->filterValues = new \SplObjectStorage();
    }

    /**
     * @param FilterValue $filterValue
     * @return FilterValueCollection
     */
    public function addFilterValue(FilterValue $filterValue)
    {
        $this->filterValues->attach($filterValue);

        return $this;
    }

    /**
     * @param FilterValue $filterValue
     * @return FilterValueCollection
     */
    public function removeFilterValue(FilterValue $filterValue)
    {
        $this->filterValues->detach($filterValue);

        return $this;
    }

    /**
     * @return \SplObjectStorage
     */
    public function getFilterValues()
    {
        return $this->filterValues;
    }
}