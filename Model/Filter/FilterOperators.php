<?php

namespace FL\QBJSParserBundle\Model\Filter;

class FilterOperators
{
    const VALID_OPERATORS = [
        'equal', 'not_equal', 'is_null', 'is_not_null',
        'less', 'less_or_equal', 'greater', 'greater_or_equal', 'between', 'not_between',
        'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with', 'is_empty', 'is_not_empty',
        'in', 'not_in',
    ];

    /**
     * @var string[]
     */
    private $operators = [];

    /**
     * @return string[]
     */
    public function getOperators(): array
    {
        return $this->operators;
    }

    /**
     * @param string[] $operators
     *
     * @return FilterOperators
     *
     * @throws \InvalidArgumentException
     */
    public function setOperators(array $operators): self
    {
        foreach ($operators as $operator) {
            $this->validateOperator($operator);
        }
        $this->operators = $operators;

        return $this;
    }

    /**
     * @param string $operator
     *
     * @return FilterOperators
     *
     * @throws \InvalidArgumentException
     */
    public function addOperator(string $operator): self
    {
        $this->validateOperator($operator);
        if (!in_array($operator, $this->operators)) {
            $this->operators[] = $operator;
        }

        return $this;
    }

    /**
     * @param string $operator
     *
     * @return FilterOperators
     *
     * @throws \InvalidArgumentException
     */
    public function removeOperator(string $operator): self
    {
        $this->validateOperator($operator);
        if (false !== ($key = array_search($operator, $this->operators))) {
            unset($this->operators[$key]);
        }

        return $this;
    }

    /**
     * @return FilterOperators
     */
    public function clearOperators(): self
    {
        $this->operators = [];

        return $this;
    }

    /**
     * @param string
     *
     * @throws \InvalidArgumentException
     */
    private function validateOperator(string $operator)
    {
        if (!in_array($operator, self::VALID_OPERATORS)) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid operator'), $operator);
        }
    }
}
