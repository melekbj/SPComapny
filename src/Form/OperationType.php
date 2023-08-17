<?php

namespace App\Form;

use App\Entity\Operation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class OperationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('description')
            ->add('soldeR')
            ->add('soldeAM')
            // ->add('compte')
            ->add('save', SubmitType::class, [
                'attr' => [
                    'class' => ''
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Operation::class,
        ]);
    }
}
