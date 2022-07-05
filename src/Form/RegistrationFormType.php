<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;


class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                ['label' => 'register.email']
            )
            ->add(
                'username',
                TextType::class,
                ['label' => 'register.username']
            )
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => 'register.agreeterms',
                'constraints' => [
                    new IsTrue([
                        'message' => 'message.agreeterms',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'message.password.match',
                'options' => ['attr' => ['class' => 'password-field']],
                'first_options'  => ['label' => 'register.password'],
                'second_options' => ['label' => 'register.repeatpassword'],
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'message.password.required',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.{6,}$)(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
                        'match' => true,
                        'message' => 'message.password.validity',
                    ])
                ],
            ])

            ->add('avatar', FileType::class, [
                'label' => 'Avatar',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            "image/png",
                            "image/jpg",
                            "image/jpeg",
                            "image/gif",
                            "image/webp"
                        ],
                        'mimeTypesMessage' => 'message.avatar',
                    ])
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
