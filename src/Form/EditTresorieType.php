<?php

namespace App\Form;

use App\Entity\Devise;
use App\Entity\Banques;
use App\Entity\Tresorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EditTresorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('soldeR')
            ->add('montant')
            ->add('description')
            // ->add('type')
            ->add('type', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices'  => [
                    'USER' => 'ROLE_USER',
                    // 'ADMIN' => 'ROLE_ADMIN',
                    'SUPER_USER' => 'ROLE_SUPER_USER',
                    
                ],
            ])
            ->add('devise',EntityType::class
               , [
                 'class' => Devise::class,
                 'choice_label' => 'nom',
                 'label' => 'Devise Type',
                 'placeholder' => 'Choisir une devise',
                 'required' => true,
                 'attr' => [
                    'class' => 'form-control'
                ]
                
            ])
            ->add('type', ChoiceType::class, [
                'label' => "Type d'opération",
                'choices' => [
                    'Entrée ' => 'entree',
                    'Sortie' => 'sortie',
                ],
                'expanded' => false, // Set to true if you want radio buttons instead of a dropdown
                'multiple' => false, // Set to true if you want to allow multiple currency selections
                'attr' => [
                    'class' => 'form-control', // Use the same CSS class for consistent styling
                ],
            ])
            // ->add('banque', EntityType::class, [
            //     'class' => Banques::class,
            //     'choice_label' => 'nom',
            //     'label' => 'Banque',
            //     'placeholder' => 'Choisir une banque',
            //     'required' => true,
            //     'query_builder' => function ($repository) use ($options) {
            //         $paysId = $options['pays_id'];

            //         return $repository->createQueryBuilder('b')
            //             ->where('b.pays = :paysId')
            //             ->setParameter('paysId', $paysId);
            //     },
            //     'attr' => [
            //         'class' => 'form-control '
            //     ]
            // ])
            // ->add('pays')
            ->add('save', SubmitType::class, [
                'label' => 'Modifier', // Add the label here
                'attr' => [
                    'class' => 'mt-3 btn btn-block btn-success btn-lg font-weight-medium auth-form-btn'
                ]
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tresorie::class,
            'pays_id' => null,
        ]);
    }
}
