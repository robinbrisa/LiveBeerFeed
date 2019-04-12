<?php

namespace App\Form\Beer;

use App\Entity\Beer\LocalBeer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class LocalBeerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name', TextType::class, ['label' => 'event.taplist_management.local_beer.name', 'translation_domain' => 'messages', 'attr' => ['class' => 'form-control-sm']])
        ->add('brewery', TextType::class, ['label' => 'event.taplist_management.local_beer.brewery', 'translation_domain' => 'messages', 'attr' => ['class' => 'form-control-sm']])
        ->add('style', TextType::class, ['label' => 'event.taplist_management.local_beer.style', 'translation_domain' => 'messages', 'attr' => ['class' => 'form-control-sm']])
        ->add('abv', NumberType::class, ['label' => 'event.taplist_management.local_beer.abv', 'translation_domain' => 'messages', 'attr' => ['class' => 'form-control-sm']])
        ->add('ibu', NumberType::class, ['label' => 'event.taplist_management.local_beer.ibu', 'translation_domain' => 'messages', 'attr' => ['class' => 'form-control-sm']])
        ->add('extra_info', TextType::class, ['label' => 'event.taplist_management.local_beer.extra_info', 'translation_domain' => 'messages', 'attr' => ['class' => 'form-control-sm']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LocalBeer::class,
        ]);
    }
}
