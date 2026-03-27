<?php

namespace App\Form;

use App\Entity\Usuario;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Nombre de usuario',
                'attr'  => [
                    'placeholder'  => 'mínimo 3 caracteres',
                    'autocomplete' => 'username',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Introduce un nombre de usuario.']),
                    new Length([
                        'min'        => 3,
                        'max'        => 180,
                        'minMessage' => 'El nombre de usuario debe tener al menos {{ limit }} caracteres.',
                        'maxMessage' => 'El nombre de usuario no puede superar {{ limit }} caracteres.',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9_.-]+$/',
                        'message' => 'Solo se permiten letras, números, guiones y puntos.',
                    ]),
                ],
            ])

            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr'  => [
                    'placeholder'  => 'tu@email.com',
                    'autocomplete' => 'email',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Introduce un email.']),
                ],
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'mapped'          => false,
                'invalid_message' => 'Las contraseñas no coinciden.',
                'first_options'   => [
                    'label' => 'Contraseña',
                    'attr'  => [
                        'placeholder'  => 'mínimo 8 caracteres',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'second_options' => [
                    'label' => 'Repetir contraseña',
                    'attr'  => [
                        'placeholder'  => 'repite la contraseña',
                        'autocomplete' => 'new-password',
                    ],
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Introduce una contraseña.']),
                    new Length([
                        'min'        => 8,
                        'max'        => 4096,
                        'minMessage' => 'La contraseña debe tener al menos {{ limit }} caracteres.',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[A-Za-z])(?=.*\d).+$/',
                        'message' => 'La contraseña debe contener al menos una letra y un número.',
                    ]),
                ],
            ])

            ->add('aceptaTerminos', CheckboxType::class, [
                'label'    => 'Acepto la política de privacidad y los términos de uso.',
                'mapped'   => false,
                'constraints' => [
                    new IsTrue(['message' => 'Debes aceptar los términos para continuar.']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Usuario::class,
        ]);
    }
}