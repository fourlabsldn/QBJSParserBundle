<?php

namespace FL\QBJSParserBundle\Model\Filter;

class FilterInput
{
    const INPUT_TYPE_TEXT = 'text';

    const INPUT_TYPE_TEXTAREA = 'textarea';

    const INPUT_TYPE_RADIO = 'radio';

    const INPUT_TYPE_CHECKBOX = 'checkbox';

    const INPUT_TYPE_SELECT = 'select';

    const VALID_INPUT_TYPES = [
        self::INPUT_TYPE_TEXT, self::INPUT_TYPE_TEXTAREA, self:: INPUT_TYPE_RADIO,
        self::INPUT_TYPE_CHECKBOX, self::INPUT_TYPE_SELECT,
    ];

    const INPUT_TYPES_REQUIRE_NO_VALUES = [
        self::INPUT_TYPE_TEXT, self::INPUT_TYPE_TEXTAREA,
    ];

    const INPUT_TYPES_REQUIRE_MULTIPLE_VALUES = [
        self:: INPUT_TYPE_RADIO, self::INPUT_TYPE_CHECKBOX, self::INPUT_TYPE_SELECT,
    ];

    /**
     * @var string
     */
    private $inputType;

    /**
     * @param string $inputType
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $inputType)
    {
        $this->inputType = $inputType;
        $this->validate();
    }

    /**
     * @return string
     */
    public function getInputType(): string
    {
        return $this->inputType;
    }

    /**
     * @param string $inputType
     *
     * @return FilterInput
     */
    public function setInputType(string $inputType): self
    {
        $this->inputType = $inputType;
        $this->validate();

        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function validate()
    {
        if (!in_array($this->inputType, self::VALID_INPUT_TYPES)) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid input type'), $this->inputType);
        }
    }
}
