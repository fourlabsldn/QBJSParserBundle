<?php

namespace FL\QBJSParserBundle\Service;

use FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup;
use FL\QBJSParser\Parser\Doctrine\DoctrineParser;
use FL\QBJSParser\Serializer\JsonDeserializer;

class JsonQueryParser
{
    /**
     * Class is the $className argument used when constructing @see DoctrineParser
     * PropertiesMapping is the $fieldsToProperties when constructing @see DoctrineParser.
     *
     * @var array
     */
    private $classToPropertiesMapping = [];

    /**
     * Class is the $className argument used when constructing @see DoctrineParser
     * AssociationMapping is the $fieldPrefixesToClasses when constructing @see DoctrineParser.
     *
     * @var array
     */
    private $classToAssociationMapping = [];

    /**
     * Class is the $className argument used when constructing @see DoctrineParser
     * EmbeddablesPropertiesMapping is the $embeddableFieldsToProperties when constructing @see DoctrineParser.
     *
     * @var array
     */
    private $classToEmbeddablesPropertiesMapping = [];

    /**
     * Class is the $className argument used when constructing @see DoctrineParser
     * EmbeddablesAssociationMapping is the $embeddableFieldPrefixesToClasses when constructing @see DoctrineParser.
     *
     * @var array
     */
    private $classToEmbeddablesAssociationMapping = [];

    /**
     * Class is the $className argument used when constructing @see DoctrineParser
     * EmbeddablesEmbeddableMapping is the $embeddableFieldPrefixesToEmbeddableClasses when constructing @see DoctrineParser.
     *
     * @var array
     */
    private $classToEmbeddablesEmbeddableMapping = [];

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
        foreach ($classesAndMappings as $classAndMappings) {
            foreach ($classAndMappings['properties'] as $field => $entityProperty) {
                $this->classToPropertiesMapping[$classAndMappings['class']][$field] = $entityProperty ? $entityProperty : $field;
            }
            foreach ($classAndMappings['association_classes'] as $prefix => $class) {
                $this->classToAssociationMapping[$classAndMappings['class']][$prefix] = $class;
            }
            foreach ($classAndMappings['embeddables_properties'] as $field => $embeddableProperty) {
                $this->classToEmbeddablesPropertiesMapping[$classAndMappings['class']][$field] = $embeddableProperty ? $embeddableProperty : $field;
            }
            foreach ($classAndMappings['embeddables_association_classes'] as $prefix => $class) {
                $this->classToEmbeddablesAssociationMapping[$classAndMappings['class']][$prefix] = $class;
            }
            foreach ($classAndMappings['embeddables_embeddable_classes'] as $prefix => $class) {
                $this->classToEmbeddablesEmbeddableMapping[$classAndMappings['class']][$prefix] = $class;
            }
        }
        foreach ($this->classToPropertiesMapping as $className => $fieldsToProperties) {
            $this->classNameToDoctrineParser[$className] = new DoctrineParser(
                $className,
                $fieldsToProperties,
                $this->classToAssociationMapping[$className] ?? [],
                $this->classToEmbeddablesPropertiesMapping[$className] ?? [],
                $this->classToEmbeddablesAssociationMapping[$className] ?? [],
                $this->classToEmbeddablesEmbeddableMapping[$className] ?? []
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
    public function parseJsonString(string $jsonString, string $entityClassName, array $sortColumns = null) : ParsedRuleGroup
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
            throw new \DomainException(sprintf(
                'You have requested a Doctrine Parser for %s, but you have not defined a mapping for it in your configuration',
                $className
            ));
        }

        return $this->classNameToDoctrineParser[$className];
    }
}
