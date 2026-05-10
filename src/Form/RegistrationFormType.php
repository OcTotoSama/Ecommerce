<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
            ])

            ->add('name', TextType::class, [
                'label' => 'Prénom',
                'attr'  => ['class' => 'form-control'],
            ])

            ->add('surname', TextType::class, [
                'label' => 'Nom',
                'attr'  => ['class' => 'form-control'],
            ])

            ->add('address', TextType::class, [
                'label' => 'Adresse',
                'attr'  => ['class' => 'form-control'],
            ])

            ->add('postalCode', IntegerType::class, [
                'label' => 'Code postal',
                'attr'  => ['class' => 'form-control'],
            ])

            ->add('city', TextType::class, [
                'label' => 'Ville',
                'attr'  => ['class' => 'form-control'],
            ])

            ->add('country', TextType::class, [
                'label' => 'Pays',
                'data'  => 'France',
                'attr'  => ['class' => 'form-control'],
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type'             => PasswordType::class,
                'mapped'           => false,
                'invalid_message'  => 'Les mots de passe ne correspondent pas.',

                'first_options' => [
                    'label' => 'Mot de passe',
                    'attr'  => [
                        'class'        => 'form-control',
                        'autocomplete' => 'new-password',
                    ],
                ],

                'second_options' => [
                    'label' => 'Confirmer le mot de passe',
                    'attr'  => [
                        'class'        => 'form-control',
                        'autocomplete' => 'new-password',
                    ],
                ],

                'constraints' => [
                    new NotBlank([
                        'message' => 'Le mot de passe est obligatoire',
                    ]),
                    new Length([
                        'min'        => 12,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                        'max'        => 4096,
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{12,}$/',
                        'message' => 'Le mot de passe doit contenir : majuscule, minuscule, chiffre et caractère spécial',
                    ]),
                ],
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped'      => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}