<?php

namespace FL\QBJSParserBundle\Model;

class SelectParserQuery
{
    /**
     * @var ParserQuery
     */
    private $parserQuery;

    /**
     * @return ParserQuery
     */
    public function getParserQuery(): ParserQuery
    {
        return $this->parserQuery;
    }

    /**
     * @param ParserQuery $parserQuery
     * @return SelectParserQuery
     */
    public function setParserQuery(ParserQuery $parserQuery): SelectParserQuery
    {
        $this->parserQuery = $parserQuery;

        return $this;
    }
}
