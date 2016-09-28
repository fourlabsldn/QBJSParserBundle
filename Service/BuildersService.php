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
     * @var QBJSDoctrineParserService
     */
    private $qbjsParser;

    /**
     * ParserQueryService constructor.
     * @param array $builders
     * @param QBJSDoctrineParserService $qbjsParser
     */
    public function __construct(array $builders, QBJSDoctrineParserService $qbjsParser)
    {
        foreach ($builders as $builderId => $builder) {
            $builder['id'] = $builderId;
            $parserQuery = new Builder();
            $parserQuery
                ->setClassName($builder['class'])
                ->setHumanReadableName($builder['human_readable_name'])
            ;
            unset($builder['class']);
            unset($builder['human_readable_name']);
            $parserQuery->setJsonString(json_encode($builder));
            $this->builders[] = $parserQuery;
        }
    }

    private function validateAgainstParser()
    {

    }

    /**
     * @return Builder[]
     */
    public function getBuilders()
    {
        return $this->builders;
    }
}