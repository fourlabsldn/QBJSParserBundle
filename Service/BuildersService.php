<?php

namespace FL\QBJSParserBundle\Service;

use FL\QBJSParserBundle\Event\Filter\InputSetEvent;
use FL\QBJSParserBundle\Event\Filter\OperatorsSetEvent;
use FL\QBJSParserBundle\Event\Filter\ValuesSetEvent;
use FL\QBJSParserBundle\Model\Builder;
use FL\QBJSParserBundle\Model\Filter\FilterInput;
use FL\QBJSParserBundle\Model\Filter\FilterOperators;
use FL\QBJSParserBundle\Model\Filter\FilterValueCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BuildersService
{
    /**
     * @var Builder[]
     */
    private $builders;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * ParserQueryService constructor.
     * @param array $buildersConfig
     * @param array $classesAndMappings
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(array $buildersConfig, array $classesAndMappings, EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher; // important that this goes before it's being used later in the constructor
        $this->validate($buildersConfig, $classesAndMappings);
        foreach ($buildersConfig as $builderId => $config) {
            $config['id'] = $builderId; // necessary for jQuery Query Builder
            $config['filters'] = $this->filtersDefaultOperators($config['filters']);
            $config['filters'] = $this->filtersOverrides($config['filters'], $builderId);
            $config['filters'] = $this->filtersBooleanOverride($config['filters']); // override all booleans to display the same!
            $builder = new Builder();
            $builder
                ->setClassName($config['class'])
                ->setHumanReadableName($config['human_readable_name'])
            ;
            unset($config['class']);
            unset($config['human_readable_name']);
            $builder->setJsonString(json_encode($config));
            $this->builders[] = $builder;
        }
        $this->dispatcher = $dispatcher;
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

                    foreach ($config['filters'] as $filter) {
                        if (! array_key_exists($filter['id'], $mappingProperties)) {
                            throw new \InvalidArgumentException(sprintf(
                                'Builders Configuration: Invalid Mapping for filter with ID %s, in builder with ID %s ',
                                $filter['id'],
                                $builderId
                            ));
                        }
                    }
                }
            }

            if (!$mappingClassFoundForBuilderClass) {
                throw new \InvalidArgumentException(sprintf(
                    'Builder with class %s, but no corresponding mapping for this class',
                    $builderClass
                ));
            }
        }
    }

    /**
     * @param array $filters
     * @return array
     */
    private function filtersDefaultOperators(array $filters) : array
    {
        foreach ($filters as $key => $filter) {
            // give the filter default operators, according to its type
            if (
                (! array_key_exists('operators', $filter)) ||
                (empty($filter['operators']))
            ) {
                $builderType = $filter['type'];

                switch ($builderType) {
                    case 'string':
                        $filter['operators'] = [
                            'equal', 'not_equal', 'is_null', 'is_not_null',
                            'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with', 'is_empty', 'is_not_empty', // specific to strings
                        ];
                        break;
                    case 'integer':
                    case 'double':
                    case 'date':
                    case 'time':
                    case 'datetime':
                        $filter['operators'] = [
                            'equal', 'not_equal', 'is_null', 'is_not_null',
                            'less', 'less_or_equal', 'greater', 'greater_or_equal', 'between', 'not_between', // specific to numbers and dates
                        ];
                        break;
                    case 'boolean':
                        $filter['operators'] = [
                            'equal', 'not_equal', 'is_null', 'is_not_null'
                        ];
                        break;
                }
            }
            $filters[$key] = $filter;
        }

        return $filters;
    }

    /**
     * @param array $filters
     * @return array
     */
    private function filtersBooleanOverride(array $filters) : array
    {
        foreach ($filters as $key => $filter) {
            $builderType = $filter['type'];

            switch ($builderType) {
                case 'boolean':
                    $filter['values'] = [
                        1 => 'Yes',
                        0 => 'No',
                    ];
                    $filter['input'] = 'radio';
                    $filter['colors'] = [
                        1 => 'success',
                        0 => 'danger',
                    ];
                    break;
            }

            $filters[$key] = $filter;
        }

        return $filters;
    }

    /**
     * @param array $filters
     * @param string $builderId
     * @return array
     *  @throws \LogicException
     */
    private function filtersOverrides(array $filters, string $builderId) : array
    {
        foreach ($filters as $key => $filter) {
            $filterId = $filter['id'];
            $filterValueCollection = $this->filterOverrideValues($filterId, $builderId);
            $filterInput = $this->filterOverrideInput($filterId, $builderId);
            $filterOperators = $this->filterOverrideOperators($filterId, $builderId, $filter['operators']);
            $this->validateValueCollectionAgainstInput($filterValueCollection, $filterInput, $filterId, $builderId);

            $valuesArray =[];
            foreach($filterValueCollection->getFilterValues() as $filterValue){
                $valuesArray[$filterValue->getValue()] = $filterValue->getLabel();
            }
            $filters[$key]['values'] = $valuesArray;
            $filters[$key]['input'] = $filterInput->getInputType();
            $filters[$key]['operators'] = $filterOperators->getOperators();
        }

        return $filters;
    }

    /**
     * @param string $filterId
     * @param string $builderId
     * @return FilterValueCollection
     */
    private function filterOverrideValues(string $filterId, string $builderId): FilterValueCollection
    {
        $filterValueCollection = new FilterValueCollection();
        $this->dispatcher->dispatch(ValuesSetEvent::EVENT_NAME, new ValuesSetEvent($filterValueCollection, $filterId, $builderId));

        return $filterValueCollection;
    }

    /**
     * @param string $filterId
     * @param string $builderId
     * @return FilterInput
     */
    private function filterOverrideInput(string $filterId, string $builderId): FilterInput
    {
        $filterInput = new FilterInput(FilterInput::INPUT_TYPE_TEXT);
        $this->dispatcher->dispatch(InputSetEvent::EVENT_NAME, new InputSetEvent($filterInput, $filterId, $builderId));

        return $filterInput;
    }

    /**
     * @param string $filterId
     * @param string $builderId
     * @param string[] $filterOperatorsArray
     * @return FilterOperators
     */
    private function filterOverrideOperators(string $filterId, string $builderId, array $filterOperatorsArray): FilterOperators
    {
        $filterOperators = new FilterOperators();
        foreach($filterOperatorsArray as $key => $operator){
            $filterOperators->addOperator($operator);
        }
        $this->dispatcher->dispatch(OperatorsSetEvent::EVENT_NAME, new OperatorsSetEvent($filterOperators, $filterId, $builderId));

        return $filterOperators;
    }

    /**
     * @param FilterValueCollection $collection
     * @param FilterInput $input
     * @param string $filterId
     * @param string $builderId
     * @throws \LogicException
     */
    private function validateValueCollectionAgainstInput(FilterValueCollection $collection, FilterInput $input, string $filterId, string $builderId)
    {
        if (
            in_array($input->getInputType(), FilterInput::INPUT_TYPES_REQUIRE_NO_VALUES) &&
            $collection->getFilterValues()->count() !== 0
        ) {
            throw new \LogicException(sprintf(
               'Too many values found, While building, Builder with ID %s and Filter with ID %s.',
                $builderId,
                $filterId
            ));

        }
        if (
            in_array($input->getInputType(), FilterInput::INPUT_TYPES_REQUIRE_MULTIPLE_VALUES) &&
            $collection->getFilterValues()->count() === 0
        ) {
            throw new \LogicException(sprintf(
                'Not enough values found, While building, Builder with ID %s and Filter with ID %s.',
                $builderId,
                $filterId
            ));

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
