<?php

namespace FL\QBJSParserBundle\Util\Validator;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

abstract class BuildersToMappings
{
    final private function __construct()
    {
    }

    /**
     * @param array $buildersConfig
     * @param array $classesAndMappings
     */
    final public static function validate(array $buildersConfig, array $classesAndMappings)
    {
        foreach ($buildersConfig as $builderId => $config) {
            if (!array_key_exists('class', $config)) {
                throw new \InvalidArgumentException(sprintf('Builders Configuration: Expected a class in builder with ID %s', $builderId));
            }
            $builderClass = $config['class'];

            if (!class_exists($builderClass)) {
                throw new \InvalidArgumentException(sprintf('Builders Configuration: %s is not a valid class in builder with ID %s ', $builderClass, $builderId));
            }

            foreach ($config['result_columns'] as $column) {
                static::validateClassHasProperty($builderClass, $column['column_machine_name'], $builderId);
            }

            $mappingClassFoundForBuilderClass = false;
            foreach ($classesAndMappings as $classAndMapping) {
                $mappingClass = $classAndMapping['class'];
                $mappingProperties = array_merge($classAndMapping['properties'], $classAndMapping['embeddables_properties'], $classAndMapping['embeddables_inside_embeddables_properties']);
                if ($builderClass === $mappingClass) {
                    $mappingClassFoundForBuilderClass = true;

                    foreach ($config['filters'] as $filter) {
                        if (!array_key_exists($filter['id'], $mappingProperties)) {
                            throw new \InvalidArgumentException(sprintf('Builders Configuration: Invalid Mapping for filter with ID %s, in builder with ID %s ', $filter['id'], $builderId));
                        }
                    }

                    foreach ($config['result_columns'] as $column) {
                        if (!array_key_exists($column['column_machine_name'], $mappingProperties)) {
                            throw new \InvalidArgumentException(sprintf('Builders Configuration: Result Column with machine name %s, in builder with ID %s, must exist in mapping for class %s ', $column['column_machine_name'], $builderId, $mappingClass));
                        }
                    }
                }
            }

            if (!$mappingClassFoundForBuilderClass) {
                throw new \InvalidArgumentException(sprintf('Builder with class %s, but no corresponding mapping for this class', $builderClass));
            }
        }
    }

    /**
     * @param string $className
     * @param string $classProperty
     * @param string $builderId
     *
     * @see http://symfony.com/doc/current/components/property_info.html#components-property-info-extractors
     *
     * @throws \InvalidArgumentException
     */
    final private static function validateClassHasProperty(string $className, string $classProperty, string $builderId)
    {
        $propertyInfo = new PropertyInfoExtractor([new ReflectionExtractor()]);
        $properties = $propertyInfo->getProperties($className);

        if (false === strpos($classProperty, '.')) { // not yet checking associations - Property Accessor can't do this
            if (!in_array($classProperty, $properties)) {
                throw new \InvalidArgumentException(sprintf('Builder %s Bad Column Declared. Property %s is not accessible in %s.', $builderId, $classProperty, $className));
            }
        }
    }
}
