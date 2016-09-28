<?php

namespace FL\QBJSParserBundle\Service;

use FL\QBJSParserBundle\Model\Builder;

class BuildersService
{
    /**
     * @var Builder[]
     */
    private $builders;

    /**
     * ParserQueryService constructor.
     * @param array $buildersConfig
     * @param array $classesAndMappings
     */
    public function __construct(array $buildersConfig, array $classesAndMappings)
    {
        $this->validate($buildersConfig, $classesAndMappings);
        foreach ($buildersConfig as $builderId => $builderConfig) {
            $builderConfig['id'] = $builderId;
            $buildersConfig['filters'] = $this->filtersDefaultOperators($buildersConfig['filters']);
            $builder = new Builder();
            $builder
                ->setClassName($builderConfig['class'])
                ->setHumanReadableName($builderConfig['human_readable_name'])
            ;
            unset($builderConfig['class']);
            unset($builderConfig['human_readable_name']);
            $builder->setJsonString(json_encode($builderConfig));
            $this->builders[] = $builder;
        }
    }

    /**
     * @param array $buildersConfig
     * @param array $classesAndMappings
     */
    private function validate(array $buildersConfig, array $classesAndMappings)
    {
        foreach ($buildersConfig as $builderId => $config) {
            if (! array_key_exists('class', $config)) {
                throw new \InvalidArgumentException(sprintf(
                    'Builders Configuration: Expected a class in builder with ID %s',
                    $builderId
                ));
            }
            $builderClass = $config['class'];

            if (! class_exists($builderClass)) {
                throw new \InvalidArgumentException(sprintf(
                    'Builders Configuration: %s is not a valid class in builder with ID %s ',
                    $builderClass,
                    $builderId
                ));
            }

            $mappingClassFoundForBuilderClass = false;
            foreach ($classesAndMappings as $classAndMapping) {
                $mappingClass = $classAndMapping['class'];
                $mappingProperties = $classAndMapping['properties'];
                if ($builderClass === $mappingClass) {
                    $mappingClassFoundForBuilderClass = true;

                    foreach($config['filters'] as $filter){
                        if (! array_key_exists($filter['id'], $mappingProperties) ){
                            throw new \InvalidArgumentException(sprintf(
                                'Builders Configuration: Invalid Mapping for filter with ID %s, in builder with ID %s ',
                                $filter['id'],
                                $builderId
                            ));
                        }
                    }
                }
            }

            if ( !$mappingClassFoundForBuilderClass) {
                throw new \InvalidArgumentException(sprintf(
                    'Builder with class %s, but no corresponding mapping for this class',
                    $builderClass
                ));
            }
        }
    }

    /**
     * @param array $builderConfigFilters
     * @return array
     */
    private function filtersDefaultOperators(array $builderConfigFilters) : array
    {
        foreach ($builderConfigFilters as $key => $filter) {
            // give the filter default operators, according to its type
            if (! array_key_exists('operators', $filter)) {
                $builderType = $filter['type'];

                switch ($builderType) {
                    case 'string' :
                        $filter['operators'] = [
                            'equal', 'not_equal', 'is_null', 'is_not_null',
                            'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with', 'is_empty', 'is_not_empty', // specific to strings
                        ];
                        break;
                    case 'integer' :
                    case 'double' :
                    case 'date' :
                    case 'time' :
                    case 'datetime' :
                    $filter['operators'] = [
                            'equal', 'not_equal', 'is_null', 'is_not_null',
                            'less', 'less_or_equal', 'greater', 'greater_or_equal', 'between', 'not_between', // specific to numbers and dates
                        ];
                        break;
                    case 'boolean' :
                        $filter['operators'] = [
                            'equal', 'not_equal',
                            'is_null', 'is_not_null'
                        ];
                        break;
                }
            }
            $builderConfigFilters[$key] = $filter;
        }


        return $builderConfigFilters;
    }

    /**
     * @return Builder[]
     */
    public function getBuilders()
    {
        return $this->builders;
    }
}