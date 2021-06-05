<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => array_flip(Address::TYPE),
                'expanded' => true,
                'required' => true,
            ])
            ->add('civility', ChoiceType::class, [
                'choices' => array_flip(Address::CIVILITY),
                'required' => false,
                'placeholder' => 'Choisissez',
            ])
            ->add('firstname', TextType::class, [
                'required' => false,
            ])
            ->add('lastname', TextType::class, [
                'required' => false,
            ])
            ->add('socialReason', TextType::class, [
                'required' => false,
            ])
            ->add('siren', TextType::class, [
                'required' => false,
                'label' => 'SIREN/SIRET'
            ])
            ->add('street', TextType::class, [
                'required' => true,
            ])
            ->add('complement', TextType::class, [
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'required' => true,
            ])
            ->add('postalCode', TextType::class, [
                'required' => true,
            ])
            ->add('countryCode', CountryType::class, [
                'required' => true,
                'placeholder' => 'Choisissez un pays dans la liste',
            ])
        ;
        if ($options['required']) {
            $builder
                ->add('phoneNumber', TelType::class, [
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                    ]
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
