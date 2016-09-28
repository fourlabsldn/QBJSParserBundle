<?php

namespace FL\QBJSParserBundle\Service;

use FL\QBJSParserBundle\Model\ParserQuery;

class ParserQueryService
{
    /**
     * @var ParserQuery[]
     */
    private $parserQueries;

    /**
     * ParserQueryService constructor.
     * @param array $queryGenerators
     */
    public function __construct(array $queryGenerators)
    {
        $parserQueries = [];
        foreach ($queryGenerators as $generatorId => $generator) {
            $generator['id'] = $generatorId;
            $parserQuery = new ParserQuery();
            $parserQuery->setClassName($generator['class']);
            unset($generator['class']);
            $parserQuery->setJsonString(json_encode($generator));
            $parserQueries[] = $parserQuery;
        }
    }

    /**
     * @return ParserQuery[]
     */
    public function getParserQueries()
    {
        return $this->parserQueries;
    }
}