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
        foreach ($builders as $generatorId => $generator) {
            $generator['id'] = $generatorId;
            $parserQuery = new Builder();
            $parserQuery
                ->setClassName($generator['class'])
                ->setHumanReadableName($generator['human_readable_name'])
            ;
            unset($generator['class']);
            unset($generator['human_readable_name']);
            $parserQuery->setJsonString(json_encode($generator));
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