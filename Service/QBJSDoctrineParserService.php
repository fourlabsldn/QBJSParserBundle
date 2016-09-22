<?php

namespace FL\QBJSParserBundle\Service;

use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Serializer\JsonDeserializer;

class QBJSDoctrineParserService
{
    /**
     * @var array
     * Class Name is the $className argument used when constructing @see DoctrineParser
     * Mapping is the $queryBuilderFieldsToEntityProperties when constructing @see DoctrineParser
     */
    private $classNameToMapping;

    /**
     * @var DoctrineParser[]
     */
    private $classNameToDoctrineParser;

    /**
     * @var JsonDeserializer
     */
    private $jsonDeserializer;

    /**
     * @param array $classesAndMappings
     * @param JsonDeserializer $jsonDeserializer
     */
    public function __construct(array $classesAndMappings, JsonDeserializer $jsonDeserializer)
    {
        foreach($classesAndMappings as $classAndMappings){
            foreach($classAndMappings['properties'] as $queryBuilderId => $entityProperty){
                $this->classNameToMapping[$classAndMappings['class']][$queryBuilderId] = $entityProperty ? $entityProperty : $queryBuilderId;
            }
        }
        foreach($this->classNameToMapping as $className => $queryBuilderFieldsToEntityProperties){
            $this->classNameToDoctrineParser[$className] = new DoctrineParser($className, $queryBuilderFieldsToEntityProperties);
        }

        $this->jsonDeserializer = $jsonDeserializer;
    }

    /**
     * @param string $jsonString
     * @param string $entityClassName
     * @return array|\FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup
     */
    public function parseJsonString(string $jsonString, string $entityClassName)
    {
        $doctrineParser = $this->newParser($entityClassName);
        return $doctrineParser->parse($this->jsonDeserializer->deserialize($jsonString));
    }


    /**
     * @param string $className
     * @return DoctrineParser
     * @throws \DomainException
     */
    private function newParser(string $className){
        if(!array_key_exists($className, $this->classNameToDoctrineParser)){
            throw new \DomainException(sprintf(
                'You have requested a Doctrine Parser for %s, but you have not defined a mapping for it in your configuration',
                $className
            ));
        }
        return $this->classNameToDoctrineParser[$className];
    }
}