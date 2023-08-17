<?php

namespace App\Form;

use App\Entity\Compte;
use App\Entity\Devise;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CompteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('num')
            ->add('solde')
            ->add('devise',EntityType::class
               , [
                 'class' => Devise::class,
                 'choice_label' => 'nom',
                 'label' => 'Devise',
                 'placeholder' => 'Choisir une devise',
                 'required' => true,
                 'attr' => [
                    'class' => 'form-control'
                ]
                
            ])
            ->add('save', SubmitType::class, [
                'attr' => [
                    'class' => ''
                ]
            ])
            // ->add('banques')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Compte::class,
        ]);
    }
}
