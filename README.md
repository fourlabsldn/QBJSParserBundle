#QBJSParserBundle

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