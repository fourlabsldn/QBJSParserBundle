# QBJSParserBundle

[![StyleCI](https://styleci.io/repos/68914794/shield?branch=master)](https://styleci.io/repos/68914794)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/fourlabsldn/QBJSParserBundle/master/LICENSE)

This is a Symfony bundle, which can be used with [jQuery QueryBuilder](http://querybuilder.js.org/). 
- It will **parse JSON coming from the frontend, and let you execute it as a Doctrine ORM query**.
- It will **give you JSON to generate the frontend**, based on your Doctrine ORM entities and configuration. 
- It is based on [QBJSParser](https://github.com/fourlabsldn/QBJSParser), which can be used without Symfony.

It has two useful services:

- `fl_qbjs_parser.json_query_parser.doctrine_orm_parser` based on class `FL\QBJSParserBundle\Service\JsonQueryParser\DoctrineORMParser`
    - This will parse a `$jsonString` coming from JQuery QueryBuilder, and a `$className`, into a `FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup`.
    - The `ParsedRuleGroup` has two properties, `$dqlString` and `$parameters`, accessible via getters. 
    - Use the `ParsedRuleGroup` properties, to create a Doctrine Query. 
    - This service is to be used with DoctrineORM.
    - This service implements `JsonQueryParserInterface`. More parsers could exist for other ORMs / ODMs.
- `fl_qbjs_parser.builders` based on class `FL\QBJSParserBundle\Service\JavascriptBuilders`
    - Use the service's `getBuilders()`, to fetch an array of `FL\QBJSParserBundle\Model\Builder\Builder` instances.
    - Each `Builder` comes with five properties, accessible via getters, `$className`, `$jsonString`, `$humanReadableName`, `$builderId`, and `$resultColumns`.
    - Use the properties of a `Builder`, to instantiate a JQuery Query Builder in your front-end.

### Installation

- `composer require fourlabs/qbjs-parser-bundle`
- Add the Bundle to app/AppKernel.php

```php
<?php

    //...
    $bundles = [
        //...
        new FL\QBJSParserBundle\FLQBJSParserBundle(),
    ];
```
- Set up configuration, as detailed below.

### Configuration Example

```yml
fl_qbjs_parser:
    builders: # these are used for service fl_qbjs_parser.builders
        product_report_builder:
            class: AppBundle\Entity\Product # this class must exist in doctrine_class_and_mappings
            human_readable_name: 'Product Report Builder'
            # result_columns
            # Not being used inside the bundle, but you can use them in your own way 
            # Make sure not to use OnetoMany or ManyToMany properties here. That makes no sense!
            # I.e. You can use direct properties of the class, ManyToOne, and OneToOne properties.
            result_columns: 
                -
                    column_machine_name: id
                    column_human_readable_name: ID
                -
                    column_machine_name: period.startDate
                    column_human_readable_name: Interview Start
                -
                    column_machine_name: period.endDate
                    column_human_readable_name: Interview End
            filters:
                -
                    id: specification.description
                    label: 'Product Specification: Description'
                    type: string # string, integer, double, date, time, datetime, boolean
                    # omit operators and get sensible defaults
                    # string operators [equal, not_equal, is_null, is_not_null,begins_with, not_begins_with, contains, not_contains, ends_with, not_ends_with, is_empty, is_not_empty]
                    # numeric/date operators [equal, not_equal, is_null, is_not_null, less, less_or_equal, greater, greater_or_equal, between, not_between]
                    # boolean operators [equal, not_equal, is_null, is_not_null]
                -
                    id: price
                    label: 'Product Price'
                    type: double
                    operators: [equal, not_equal, less, less_or_equal, greater, greater_or_equal, between, not_between, is_null, is_not_null]

                -
                    id: availability.startDate
                    label: 'Product Availability - Start Date'
                    type: datetime
    # these are used for service fl_qbjs_parser.json_query_parser.doctrine_orm_parser
    # if another orm is being used, omit this key
    doctrine_classes_and_mappings: 
        app_entity_product: # this key is for organizational purposes only
            class: AppBundle\Entity\Product # Class Name of a Doctrine Entity
            properties: # required
                # Keys sent by QueryBuilderJS in a jsonString
                # Values should be visible property (public or by getter) in your entity
                # They can also be associations and their properties
                # Leave the value as null (~) to use the same value as the key
                id: ~
                labels.id: ~
                labels.name: ~
                labels.authors.id: ~
                labels.authors.address.line1: ~
                author.id: ~
            association_classes:
                # Indicate the class for each of the associations in properties
                labels: AppBundle\Entity\Label
                labels.authors: AppBundle\Entity\Author
                labels.authors.address: AppBundle\Entity\Address
                author: AppBundle\Entity\Author
            # Now supporting embeddables!
            embeddables_properties:
                availability.startDate: ~
                availability.endDate: ~
                labels.availability.startDate: ~
                labels.availability.endDate: ~
                price.amount: ~
            embeddables_inside_embeddables_properties:
                price.currency.code: ~
            embeddables_association_classes:
                labels: AppBundle\Entity\Label
            embeddables_embeddable_classes:
                availability: League\Period\Period
                labels.availability: League\Period\Period
                price: Money\Money
                price.currency: Money\Currency
```

### Usage Example 

`fl_qbjs_parser.json_query_parser.doctrine_orm_parser`

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
             $parsedRuleGroup = $this->get('fl_qbjs_parser.json_query_parser.doctrine_orm_parser')->parseJsonString($jsonString, Product::class);
             
             $query = $this->get('doctrine.orm.entity_manager')->createQuery($parsedRuleGroup->getQueryString());
             $query->setParameters($parsedRuleGroup->getParameters());
             $results = $query->execute();
             
             //...
        }
    } 
```

`fl_qbjs_parser.builders`

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
             $builders = $this->get('fl_qbjs_parser.builders')->getBuilders();
                     
             return $this->render('default/index.html.twig', [
                 'builders' => $builders,
             ]);
             
             //...
        }
    } 
```

### Events

The bundle also comes with an event, that allows you to override `fl_qbjs_parser.builders`. You can currently override values, the input type, and operators.

Here's an example of the configuration for a listener, for such an event.

```yaml
services:
    app.listener.override_builders:
        class: AppBundle\EventListener\OverrideBuildersListener
        arguments:
        tags:
            - { name: kernel.event_listener, event: fl_qbjs_parser.filter_set_event, method: onFilterSet }
```
