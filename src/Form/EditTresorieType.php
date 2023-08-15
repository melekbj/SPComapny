<?php

namespace App\Form;

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
            ->add('solde_r', null, [
                'label' => 'Solde réel',
            ])
            ->add('entree', null, [
                'label' => 'Montant entrée',
            ])
            ->add('sortie', null, [
                'label' => 'Montant sortie',
            ])
            ->add('descE', null, [
                'label' => 'Description du montant entrée',
            ])
            ->add('descS', null, [
                'label' => 'Description du montant sortie',
            ])
            ->add('deviseE', ChoiceType::class, [
                'label' => 'Devise',
                'choices' => [
                    'USD' => 'USD',
                    'EUR' => 'EUR',
                ],
                'expanded' => false, // Set to true if you want radio buttons instead of a dropdown
                'multiple' => false, // Set to true if you want to allow multiple currency selections
                'attr' => [
                    'class' => 'form-control', // Use the same CSS class for consistent styling
                ],
            ])
            ->add('deviseS', ChoiceType::class, [
                'label' => 'Devise',
                'choices' => [
                    'USD' => 'USD',
                    'EUR' => 'EUR',
                ],
                'expanded' => false, // Set to true if you want radio buttons instead of a dropdown
                'multiple' => false, // Set to true if you want to allow multiple currency selections
                'attr' => [
                    'class' => 'form-control', // Use the same CSS class for consistent styling
                ],
            ])
            
            ->add('Add', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary mt-3 '
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
