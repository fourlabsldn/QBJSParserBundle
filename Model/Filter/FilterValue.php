<?php

namespace FL\QBJSParserBundle\Model\Filter;

class FilterValue
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $label;

    /**
     * @param $value
     * @param string $label
     */
    public function __construct($value, string $label)
    {
        $this->value = $value;
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }
}
