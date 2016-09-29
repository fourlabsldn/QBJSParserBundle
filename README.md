#QBJSParserBundle

This bundle is a symfony wrapper for the QBJSParser library. It has two useful services:

- `qbjs_parser.doctrine_parser` based on class `FL\QBJSParserBundle\Service\QBJSDoctrineParserService`
    - This will parse a `$jsonString` coming from JQuery QueryBuilder, and a `$className`, into a `FL\QBJSParser\Parsed\Doctrine\ParsedRuleGroup`.
    - The `ParsedRuleGroup` has two properties, `$dqlString` and `$parameters`, accessible via getters. 
    - Use the `ParsedRuleGroup` properties, to create a Doctrine Query. 
- `qbjs_parser.filters_builders` based on class `FL\QBJSParserBundle\Service\BuildersService`
    - Use the service's `getBuilders()`, to fetch an array of `FL\QBJSParserBundle\Model\Builder` instances.
    - Each `Builder` comes with three properties, accessible via getters, `$className`, `$jsonString`, and `$humanReadableName`.
    - Use the properties of a `Builder`, to instantiate a JQuery Query Builder in your front-end.

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
    builders: # these are used for service qbjs_parser.builders
        product_report_builder:
            class: AppBundle\Entity\Product # this class must exist in doctrine_class_and_mappings
            human_readable_name: 'Product Report Builder'
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
    doctrine_classes_and_mappings: # these are used for service qbjs_parser.doctrine_parser
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
                labels: AppBundle\Entity\Label
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

`qbjs_parser.builders`

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
             $builders = $this->get('qbjs_parser.builders')->getParserQueries();
                     
             return $this->render('default/index.html.twig', [
                 'builders' => $builders,
             ]);
             
             //...
        }
    } 
```

### Events

The bundle also comes with events, that allow you to `qbjs_parser.builders`. You can currently override values, the input type, and operators.

Here's an example of the configuration for a listener, for such events.

```yaml
services:
    app.listener.override_builders:
        class: AppBundle\EventListener\OverrideBuildersListener
        arguments:
        tags:
            - { name: kernel.event_listener, event: qbjs_parser.filter_values_set, method: onValuesSet }
            - { name: kernel.event_listener, event: qbjs_parser.filter_input_set, method: onInputSet }
            - { name: kernel.event_listener, event: qbjs_parser.filter_operators_set, method: onOperatorsSet }
```