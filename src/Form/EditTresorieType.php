<?php

namespace App\Form;

use App\Entity\Banques;
use App\Entity\Tresorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            // ->add('banque')
            // ->add('banque', EntityType::class, [
            //     'class' => Banques::class,
            //     'choice_label' => 'nom',
            //     'label' => 'Banque',
            //     'placeholder' => 'Choose a type',
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
