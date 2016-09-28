#QBJSParserBundle

This bundle is a symfony wrapper for the QBJSParser library. It has two useful services:

- `qbjs_parser.doctrine_parser` based on class `FL\QBJSParserBundle\Service\QBJSDoctrineParserService`
    - This will parse a `$jsonString` coming from JQuery QueryBuilder, and a `$className`, into a `FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup`.
    - The `ParsedRuleGroup` has two properties, `$dqlString` and `$parameters`, accessible via getters. 
    - Use the `ParsedRuleGroup` properties, to create a Doctrine Query. 
- `qbjs_parser.parser_query` based on class `FL\QBJSParserBundle\Service\ParserQueryService`
    - Use the service's `getParserQueries()`, to fetch an array of `FL\QBJSParserBundle\Model\ParserQuery` instances.
    - Each `ParserQuery` comes with three properties, accessible via getters, `$className`, `$jsonString`, and `$humanReadableName`.
    - Use the properties of `ParserQuery`, to instantiate JQuery Query Builders in your front-end.

### Installation

- `composer require fourlabs/qbjs-parser-bundle`
- Add the Bundle to app/AppKernel.php

```php
<?php

    //...
    $bundles = [
        //...
        new FL\QBJSParserBundle\QBJSParserBundle(),
    ];
```
- Set up configuration, as detailed below.

### Configuration Example

```yml
qbjs_parser:
    query_generators: # these are used for the ParserQueryService
        product_report_builder:
            class: AppBundle\Entity\Product
            human_readable_name: 'Product Report Builder'
            filters:
                -
                    id: specification.description
                    label: 'Product Specification: Description'
                    type: string
                    operators: [equal, not_equal, begins_with, not_begins_with, contains, not_contains, ends_with, not_ends_with,is_empty, is_not_empty, is_null, is_not_null]
                -
                    id: price
                    label: 'Product Price'
                    type: double
                    operators: [equal, not_equal, less, less_or_equal, greater, greater_or_equal, between, not_between, is_null, is_not_null]
    doctrine_classes_and_mappings: # only if you will be using the service "qbjs_parser.doctrine_parser"
        app_entity_product: # this key is for organizational purposes only
            class: AppBundle\Entity\Product # Class Name of a Doctrine Entity
            properties: # required
                # Keys sent by QueryBuilderJS in a jsonString
                # Values should be visible property (public or by getter) in your entity
                # They can also be associations and their properties
                # Leave the value as null (~) to use the same value as the key
                id: ~
                priceValue: price
                labels.id: ~
                labels.name: ~
                labels.authors.id: ~
                labels.authors.address.line1: ~
                author.id: ~
            association_classes: # required
                # Indicate the class for each of the associations in properties
                labels: AppBundle\Entity\Labels 
                labels.authors: AppBundle\Entity\Author
                labels.authors.address: AppBundle\Entity\Address
                author: AppBundle\Entity\Author
```

### Usage Example 

`qbjs_parser.doctrine_parser`

```php
<?php
    namespace App\Controller;
    
    //...
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use AppBundle\Entity\Product;

    class ProductController extends Controller
    {
        public function reportsAction(Request $request, string $jsonString)
        {
             $parsedRuleGroup = $this->get('qbjs_parser.doctrine_parser')->parseJsonString($jsonString, Product::class);
             
             $query = $this->get('doctrine.orm.entity_manager')->createQuery($parsedRuleGroup->getDqlString());
             $query->setParameters($parsedRuleGroup->getParameters());
             $results = $query->execute();
             
             //...
        }
    } 
```

`qbjs_parser.parser_query`

```php
<?php
    namespace App\Controller;
    
    //...
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class ReportController extends Controller
    {
        public function reportBuilderAction(Request $request)
        {
             $parserQueries = $this->get('qbjs_parser.parser_query')->getParserQueries();
                     
             return $this->render('default/index.html.twig', [
                 'parser_queries' => $parserQueries,
             ]);
             
             //...
        }
    } 
```