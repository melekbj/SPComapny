<?php

namespace App\Form;

use App\Entity\Materiels;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MaterielType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ref')
            ->add('nom')
            // ->add('photo')
            ->add('imageFile', VichFileType::class, [
                'label' => 'Photo de materiel',
                'required' => false,
                'allow_delete' => false,
                'download_uri' => true,
            ])
            ->add('tauxtva')
            ->add('quantite')
            ->add('pu')
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
            'data_class' => Materiels::class,
        ]);
    }
}
