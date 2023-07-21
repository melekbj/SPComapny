<?php

namespace App\Form;

use App\Entity\CategorieMateriel;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CategorieMaterielType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle')
            ->add('imageFile', VichFileType::class, [
                'label' => 'Photo de categorie materiel',
                'required' => false,
                'allow_delete' => false,
                'download_uri' => true,
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
            'data_class' => CategorieMateriel::class,
        ]);
    }
}
