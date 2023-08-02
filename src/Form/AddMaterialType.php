<?php

namespace App\Form;

use App\Entity\Materiels;
use App\Entity\CategorieMateriel;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AddMaterialType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',null, [
                'required' => true,
            ])
            // ->add('photo')
            ->add('imageFile', VichFileType::class, [
                'label' => 'Photo de materiel',
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
            ])
            ->add('pu',null, [
                'required' => true,
            ])
            ->add('tauxtva',null, [
                'required' => true,
            ])
            ->add('quantite',null, [
                'required' => true,
            ])
            ->add('ref',null, [
                'required' => true,
            ])
            // ->add('categorie')
            ->add('categorie',EntityType::class
               , [
                 'class' => CategorieMateriel::class,
                 'choice_label' => 'libelle',
                 'label' => 'Evenement Type',
                 'placeholder' => 'Choose a type',
                 'required' => true,
                 'attr' => [
                    'class' => 'form-control'
                ]
                
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Create new',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Materiels::class,
        ]);
    }
}
