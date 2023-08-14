<?php

namespace App\Form;

use App\Entity\Banques;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class BankInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('nom')
            // ->add('photo')
            ->add('responsable')
            ->add('address')
            ->add('tel')
            ->add('mail')
            ->add('descr')
            // ->add('pays')
            ->add('Confirmer', SubmitType::class, [
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
