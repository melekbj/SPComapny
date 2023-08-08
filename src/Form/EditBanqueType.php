<?php

namespace App\Form;

use App\Entity\Pays;
use App\Entity\Banques;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class EditBanqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('descr')
            ->add('mail')
            ->add('tel')
            ->add('address')
            // ->add('photo')
            ->add('pays',EntityType::class
            ,[
             'class' => Pays::class,
             'choice_label' => 'nom',
            'label' => 'Country',
                'placeholder' => 'Choose a type',
                'required' => true,
            
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
            'data_class' => Banques::class,
        ]);
    }
}
