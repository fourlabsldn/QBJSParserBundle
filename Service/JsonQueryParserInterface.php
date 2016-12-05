<?php

namespace FL\QBJSParserBundle\Service;

use FL\QBJSParser\Parsed\AbstractParsedRuleGroup;

interface JsonQueryParserInterface
{
    /**
     * @param string     $jsonString
     * @param string     $entityClassName
     * @param array|null $sortColumns
     *
     * @return AbstractParsedRuleGroup
     */
    public function parseJsonString(string $jsonString, string $entityClassName, array $sortColumns = null);
}
