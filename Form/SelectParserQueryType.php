<?php

namespace FL\QBJSParserBundle\Form;

use FL\QBJSParserBundle\Model\ParserQuery;
use FL\QBJSParserBundle\Model\SelectParserQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectParserQueryType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['choices'] as $choice) {
            if( !($choice instanceof ParserQuery) ){
                throw new \InvalidArgumentException(sprintf(
                    'All choices in %s must be an instance of %s',
                    SelectParserQueryType::class,
                    ParserQuery::class
                ));
            }
        }
        $builder
            ->add('parserQuery', ChoiceType::class, [
                'required' => true,
                'choices' => $options['choices'],
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => SelectParserQuery::class]);
        $resolver->setRequired('choices');
        $resolver->setAllowedTypes('choices', 'array');
    }
}