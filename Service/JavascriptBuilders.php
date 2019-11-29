<?php

namespace FL\QBJSParserBundle\Service;

use FL\QBJSParserBundle\Event\Filter\FilterSetEvent;
use FL\QBJSParserBundle\Model\Builder\Builder;
use FL\QBJSParserBundle\Model\Filter\FilterInput;
use FL\QBJSParserBundle\Model\Filter\FilterOperators;
use FL\QBJSParserBundle\Model\Filter\FilterValueCollection;
use FL\QBJSParserBundle\Model\Builder\ResultColumn;
use FL\QBJSParserBundle\Util\Validator\BuildersToMappings;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JavascriptBuilders
{
    /**
     * Keys should equal @see Builder::$builderId.
     *
     * @var Builder[]
     */
    protected $builders;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * ParserQueryService constructor.
     *
     * @param array                    $buildersConfig
     * @param array                    $classesAndMappings
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(array $buildersConfig, array $classesAndMappings, EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher; // important that this goes before it's being used later in the constructor
        BuildersToMappings::validate($buildersConfig, $classesAndMappings);
        foreach ($buildersConfig as $builderId => $config) {
            $config['id'] = strval($builderId); // necessary for jQuery Query Builder
            $config['filters'] = $this->filtersDefaultOperators($config['filters']);
            $config['filters'] = $this->filtersOverrides($config['filters'], $builderId);
            $config['filters'] = $this->filtersBooleanOverride($config['filters']); // override all booleans to display the same!
            $config['filters'] = $this->filtersDateOverrides($config['filters']); // override all dates to display the same!
            $config['filters'] = $this->filtersOverrideValues($config['filters']); // override each filter's values
            $builder = new Builder();
            $builder
                ->setBuilderId($builderId)
                ->setClassName($config['class'])
                ->setHumanReadableName($config['human_readable_name'])
            ;
            unset($config['class']);
            unset($config['human_readable_name']);

            $builder->setJsonString(json_encode($config));

            foreach ($config['result_columns'] as $column) {
                $builder->addResultColumn(new ResultColumn($column['column_human_readable_name'], $column['column_machine_name']));
            }

            $this->builders[$builderId] = $builder;
        }
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    private function filtersDefaultOperators(array $filters): array
    {
        foreach ($filters as $key => $filter) {
            // give the filter default operators, according to its type
            if (
                (!array_key_exists('operators', $filter)) ||
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
                            'equal', 'not_equal', 'is_null', 'is_not_null',
                        ];

                        break;
                }
            }
            $filters[$key] = $filter;
        }

        return $filters;
    }

    /**
     * @param array  $filters
     * @param string $builderId
     *
     * @return array
     *
     * @throws \LogicException
     */
    private function filtersOverrides(array $filters, string $builderId): array
    {
        foreach ($filters as $key => $filter) {
            $filterId = $filter['id'];
            $filterValueCollection = new FilterValueCollection();
            $filterInput = new FilterInput(FilterInput::INPUT_TYPE_TEXT);
            $filterOperators = new FilterOperators();
            foreach ($filter['operators'] as $operator) {
                $filterOperators->addOperator($operator);
            }
            $this->dispatcher->dispatch(FilterSetEvent::EVENT_NAME, new FilterSetEvent($filterInput, $filterOperators, $filterValueCollection, $filterId, $builderId));
            $this->validateValueCollectionAgainstInput($filterValueCollection, $filterInput, $filterId, $builderId);

            $valuesArray = [];
            foreach ($filterValueCollection->getFilterValues() as $filterValue) {
                $valuesArray[$filterValue->getValue()] = $filterValue->getLabel();
            }
            $filters[$key]['values'] = $valuesArray;
            $filters[$key]['input'] = $filterInput->getInputType();
            $filters[$key]['operators'] = $filterOperators->getOperators();
        }

        return $filters;
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    private function filtersBooleanOverride(array $filters): array
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
     * Use with @link https://eonasdan.github.io/bootstrap-datetimepicker/
     * Also make sure to account for @link https://github.com/mistic100/jQuery-QueryBuilder/issues/176.
     *
     * @param array $filters
     *
     * @return array
     */
    private function filtersDateOverrides(array $filters): array
    {
        foreach ($filters as $key => $filter) {
            $builderType = $filter['type'];

            switch ($builderType) {
                case 'date':
                    $filter['validation'] = [
                        'format' => 'YYYY/MM/DD',
                    ];
                    $filter['plugin'] = 'datetimepicker';
                    $filter['plugin_config'] = [
                        'format' => 'YYYY/MM/DD',
                    ];

                    break;
                case 'datetime':
                    $filter['validation'] = [
                        'format' => 'YYYY/MM/DD HH:mm',
                    ];
                    $filter['plugin'] = 'datetimepicker';
                    $filter['plugin_config'] = [
                        'format' => 'YYYY/MM/DD HH:mm',
                    ];

                    break;
                case 'time':
                    $filter['validation'] = [
                        'format' => 'HH:mm',
                    ];
                    $filter['plugin'] = 'datetimepicker';
                    $filter['plugin_config'] = [
                        'format' => 'HH:mm',
                    ];

                    break;
            }

            $filters[$key] = $filter;
        }

        return $filters;
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    private function filtersOverrideValues(array $filters): array
    {
        foreach ($filters as $key => $filter) {
            // Php converts an array such as ["0" => "red", "1" => "green"] into [0 => "red", 1 => "green"]
            // Thus the json_encoding would be ["red", "green"] instead of {"0" : "red", "1" : "green"}
            // A way around this is casting the array as an object
            $filters[$key]['values'] = (object) $filters[$key]['values'];
        }

        return $filters;
    }

    /**
     * @param FilterValueCollection $collection
     * @param FilterInput           $input
     * @param string                $filterId
     * @param string                $builderId
     *
     * @throws \LogicException
     */
    private function validateValueCollectionAgainstInput(FilterValueCollection $collection, FilterInput $input, string $filterId, string $builderId)
    {
        if (
            in_array($input->getInputType(), FilterInput::INPUT_TYPES_REQUIRE_NO_VALUES) &&
            0 !== $collection->getFilterValues()->count()
        ) {
            throw new \LogicException(sprintf('Too many values found, While building, Builder with ID %s and Filter with ID %s.', $builderId, $filterId));
        }
        if (
            in_array($input->getInputType(), FilterInput::INPUT_TYPES_REQUIRE_MULTIPLE_VALUES) &&
            0 === $collection->getFilterValues()->count()
        ) {
            throw new \LogicException(sprintf('Not enough values found, While building, Builder with ID %s and Filter with ID %s.', $builderId, $filterId));
        }
    }

    /**
     * @return Builder[]
     */
    public function getBuilders()
    {
        return $this->builders;
    }

    /**
     * @param string $builderId
     *
     * @return Builder|null
     */
    public function getBuilderById(string $builderId)
    {
        if (array_key_exists($builderId, $this->builders)) {
            return $this->builders[$builderId];
        }

        return;
    }
}
