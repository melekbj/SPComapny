<?php

namespace App\Form;

use App\Entity\Banques;
use App\Entity\Tresorie;
use JsonToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TresorieType extends AbstractType
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
            ->add('banque', EntityType::class, [
                'class' => Banques::class,
                'choice_label' => 'nom',
                'label' => 'Banque',
                'placeholder' => 'Choisir une banque',
                'required' => true,
                'query_builder' => function ($repository) use ($options) {
                    $paysId = $options['pays_id'];

                    return $repository->createQueryBuilder('b')
                        ->where('b.pays = :paysId')
                        ->setParameter('paysId', $paysId);
                },
                'attr' => [
                    'class' => 'form-control '
                ]
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
