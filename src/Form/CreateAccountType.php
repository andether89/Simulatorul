<?php

namespace App\Form;

use App\Entity\User;
use App\Validator\PasswordMatch;
use App\Validator\PasswordStrong;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User $user */
        $user = $options['data'];
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ],
            ]);
        if (!($user->getFirstname() && $user->getLastname()) && !($user->getSiren() && $user->getSocialReason())) {
            $builder
                ->add('firstname', TextType::class, [
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                    ],
                ])
                ->add('lastname', TextType::class, [
                    'required' => true,
                    'constraints' => [
                        new NotBlank(),
                    ],
                ])
            ;
        }
        $builder
            ->add('plainPassword', PasswordType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => 'Password'
            ])
            ->add('password', PasswordType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new PasswordStrong(),
                ],
                'label' => 'Confirm password'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'constraints' => [
                new PasswordMatch(),
                new UniqueEntity([
                    'fields' => [
                        'email'
                    ]
                ])
            ],
            'translation_domain' => 'forms',
        ]);
    }
}
