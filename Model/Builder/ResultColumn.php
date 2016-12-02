<?php

namespace FL\QBJSParserBundle\Model\Builder;

class ResultColumn
{
    /**
     * @var string
     */
    private $humanReadableName;

    /**
     * @var string
     */
    private $machineName;

    /**
     * @param $humanReadableName
     * @param $machineName
     */
    public function __construct(string $humanReadableName, string $machineName)
    {
        $this->humanReadableName = $humanReadableName;
        $this->machineName = $machineName;
    }

    /**
     * @return string
     */
    public function getHumanReadableName(): string
    {
        return $this->humanReadableName;
    }

    /**
     * @return string
     */
    public function getMachineName(): string
    {
        return $this->machineName;
    }
}
