<?php

namespace App\Form;

use App\Entity\Order;
use App\Validator\AddressComplete;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('shippingAddress', AddressType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new AddressComplete(),
                ],
            ])
            ->add('billingAddress', AddressType::class, [
                'required' => false,
                'constraints' => [
                    new AddressComplete(),
                ],
            ])
            ->add('coOwners', CollectionType::class, [
                'entry_type' => CoOwnerType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'required' => false,
                'label' => 'Co-propriétaires'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
            'translation_domain' => 'forms'
        ]);
    }
}
