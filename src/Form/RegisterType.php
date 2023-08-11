<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;


class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullname')
            ->add('imageFile', VichFileType::class, [
                'label' => 'Photo de profil',
                'required' => true,
                'allow_delete' => false,
                'download_uri' => false,
                'attr' => ['class' => 'square-image-input'],
            ])
            ->add('email', EmailType::class, [
            ])
            ->add('roles', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices'  => [
                    'USER' => 'ROLE_USER',
                    // 'ADMIN' => 'ROLE_ADMIN',
                    'SUPER_USER' => 'ROLE_SUPER_USER',
                    
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'required' => true,
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Confirm Password'],
                'constraints' => [
                    new Length([
                        'min' => 8,
                        'max' => 64,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'maxMessage' => 'Your password should be at most {{ limit }} characters',
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                        'message' => 'Your password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character (@$!%*?&)',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, [
                'attr' => [
                    'class' => 'mt-3 btn btn-block btn-success btn-lg font-weight-medium auth-form-btn'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
