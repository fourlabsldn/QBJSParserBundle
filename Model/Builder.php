<?php

namespace FL\QBJSParserBundle\Model;

class Builder
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     * Containing multiple filters that can be used to instantiate a Jquery QueryBuilder
     */
    private $jsonString;

    /**
     * @var string
     */
    private $humanReadableName;

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $className
     * @return Builder
     */
    public function setClassName(string $className): Builder
    {
        $this->className = $className;

        return $this;
    }

    /**
     * @return string
     */
    public function getJsonString(): string
    {
        return $this->jsonString;
    }

    /**
     * @param string $jsonString
     * @return Builder
     */
    public function setJsonString(string $jsonString): Builder
    {
        $this->jsonString = $jsonString;

        return $this;
    }

    /**
     * @return string
     */
    public function getHumanReadableName(): string
    {
        return $this->humanReadableName;
    }

    /**
     * @param string $humanReadableName
     * @return Builder
     */
    public function setHumanReadableName(string $humanReadableName): Builder
    {
        $this->humanReadableName = $humanReadableName;

        return $this;
    }
}
