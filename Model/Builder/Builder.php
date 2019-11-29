<?php

namespace FL\QBJSParserBundle\Model\Builder;

class Builder
{
    /**
     * @var string
     */
    private $className = '';

    /**
     * Containing multiple filters that can be used to instantiate a Jquery QueryBuilder.
     *
     * @var string
     */
    private $jsonString = '';

    /**
     * @var string
     */
    private $humanReadableName = '';

    /**
     * @var string
     */
    private $builderId = '';

    /**
     * @var \SplObjectStorage
     */
    private $resultColumns;

    public function __construct()
    {
        $this->resultColumns = new \SplObjectStorage();
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $className
     *
     * @return Builder
     */
    public function setClassName(string $className): self
    {
        if (!class_exists($className)) {
            throw new \InvalidArgumentException(sprintf('Class %s does not exist', $className));
        }
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
     *
     * @return Builder
     */
    public function setJsonString(string $jsonString): self
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
     *
     * @return Builder
     */
    public function setHumanReadableName(string $humanReadableName): self
    {
        $this->humanReadableName = $humanReadableName;

        return $this;
    }

    /**
     * @return string
     */
    public function getBuilderId(): string
    {
        return $this->builderId;
    }

    /**
     * @param string $builderId
     *
     * @return Builder
     */
    public function setBuilderId(string $builderId): self
    {
        $this->builderId = $builderId;

        return $this;
    }

    /**
     * @return \SplObjectStorage
     */
    public function getResultColumns(): \SplObjectStorage
    {
        return $this->resultColumns;
    }

    /**
     * @param ResultColumn $resultColumn
     *
     * @return Builder
     */
    public function addResultColumn(ResultColumn $resultColumn): self
    {
        // prevent columns with the same machine_name or human_readable_name to be added
        foreach ($this->resultColumns as $column) {
            /** @var ResultColumn $column */
            if (
                $column->getHumanReadableName() === $resultColumn->getHumanReadableName() ||
                $column->getMachineName() === $resultColumn->getMachineName()
            ) {
                return $this;
            }
        }
        $this->resultColumns->attach($resultColumn);

        return $this;
    }

    /**
     * @param ResultColumn $resultColumn
     *
     * @return Builder
     */
    public function removeResultColumn(ResultColumn $resultColumn): self
    {
        $this->resultColumns->detach($resultColumn);

        return $this;
    }

    /**
     * @param string $machineName
     *
     * @return string|null
     */
    public function getHumanReadableWithMachineName(string $machineName)
    {
        /** @var ResultColumn $column */
        foreach ($this->resultColumns as $column) {
            if ($column->getMachineName() === $machineName) {
                return $column->getHumanReadableName();
            }
        }

        return;
    }
}
