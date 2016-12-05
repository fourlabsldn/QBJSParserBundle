<?php

namespace FL\QBJSParserBundle\Service\JsonQueryParser;

use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;
use FL\QBJSParserBundle\Service\JsonQueryParserInterface;

interface DoctrineORMParserInterface extends JsonQueryParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseJsonString(string $jsonString, string $entityClassName, array $sortColumns = null): ParsedRuleGroup;
}
