<?php

namespace App\Form;

use App\Entity\Pays;
use App\Entity\Banques;
use App\Form\PaysFormType;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class BanquesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('imageFile', VichFileType::class, [
                'label' => 'Photo de banque',
                'required' => true,
                'allow_delete' => false,
                'download_uri' => true,
            ])
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
