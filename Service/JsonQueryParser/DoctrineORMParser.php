<?php

namespace FL\QBJSParserBundle\Service\JsonQueryParser;

use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;
use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Serializer\JsonDeserializer;

class DoctrineORMParser implements DoctrineORMParserInterface
{
    /**
     * @var DoctrineParser[]
     */
    private $classNameToDoctrineParser = [];

    /**
     * @var JsonDeserializer
     */
    private $jsonDeserializer;

    /**
     * @param array            $classesAndMappings
     * @param JsonDeserializer $jsonDeserializer
     */
    public function __construct(array $classesAndMappings, JsonDeserializer $jsonDeserializer)
    {
        $classToPropertiesMapping = [];
        $classToAssociationMapping = [];
        $classToEmbeddablesPropertiesMapping = [];
        $classToEmbeddablesInsideEmbeddablesPropertiesMapping = [];
        $classToEmbeddablesAssociationMapping = [];
        $classToEmbeddablesEmbeddableMapping = [];

        foreach ($classesAndMappings as $classAndMappings) {
            $className = $classAndMappings['class'];
            foreach ($classAndMappings['properties'] as $field => $entityProperty) {
                $classToPropertiesMapping[$className][$field] = $entityProperty ? $entityProperty : $field;
            }
            foreach ($classAndMappings['association_classes'] as $prefix => $class) {
                $classToAssociationMapping[$className][$prefix] = $class;
            }
            foreach ($classAndMappings['embeddables_properties'] as $field => $embeddableProperty) {
                $classToEmbeddablesPropertiesMapping[$className][$field] = $embeddableProperty ? $embeddableProperty : $field;
            }
            foreach ($classAndMappings['embeddables_inside_embeddables_properties'] as $field => $embeddableProperty) {
                $classToEmbeddablesInsideEmbeddablesPropertiesMapping[$className][$field] = $embeddableProperty ? $embeddableProperty : $field;
            }
            foreach ($classAndMappings['embeddables_association_classes'] as $prefix => $class) {
                $classToEmbeddablesAssociationMapping[$className][$prefix] = $class;
            }
            foreach ($classAndMappings['embeddables_embeddable_classes'] as $prefix => $class) {
                $classToEmbeddablesEmbeddableMapping[$className][$prefix] = $class;
            }
        }
        foreach ($classToPropertiesMapping as $className => $fieldsToProperties) {
            $this->classNameToDoctrineParser[$className] = new DoctrineParser(
                $className,
                $fieldsToProperties,
                $classToAssociationMapping[$className] ?? [],
                $classToEmbeddablesPropertiesMapping[$className] ?? [],
                $classToEmbeddablesInsideEmbeddablesPropertiesMapping[$className] ?? [],
                $classToEmbeddablesAssociationMapping[$className] ?? [],
                $classToEmbeddablesEmbeddableMapping[$className] ?? []
            );
        }

        $this->jsonDeserializer = $jsonDeserializer;
    }

    /**
     * @param string     $jsonString
     * @param string     $entityClassName
     * @param array|null $sortColumns
     *
     * @return ParsedRuleGroup
     */
    public function parseJsonString(string $jsonString, string $entityClassName, array $sortColumns = null): ParsedRuleGroup
    {
        $doctrineParser = $this->newParser($entityClassName);

        return $doctrineParser->parse($this->jsonDeserializer->deserialize($jsonString), $sortColumns);
    }

    /**
     * @param string $className
     *
     * @return DoctrineParser
     *
     * @throws \DomainException
     */
    private function newParser(string $className)
    {
        if (!array_key_exists($className, $this->classNameToDoctrineParser)) {
            throw new \DomainException(sprintf('You have requested a Doctrine Parser for %s, but you have not defined a mapping for it in your configuration', $className));
        }

        return $this->classNameToDoctrineParser[$className];
    }
}
