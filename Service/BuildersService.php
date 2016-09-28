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
            foreach($classesAndMappings as $classAndMapping){
                $mappingClass = $classAndMapping['class'];
                $mappingProperties = $classAndMapping['properties'];
                if($builderClass === $mappingClass){
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
        }
    }

    /**
     * @return Builder[]
     */
    public function getBuilders()
    {
        return $this->builders;
    }
}