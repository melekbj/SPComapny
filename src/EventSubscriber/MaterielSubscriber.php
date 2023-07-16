<?php

namespace App\EventSubscriber;

use App\Form\ExtraFieldType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilderInterface;


class MaterielSubscriber implements EventSubscriberInterface
{
    public function onFormEventSubscriber($event): void
    {
        // ...
    }

    public function preSetData(PreSetDataEvent  $event)
    {
        $form = $event->getForm();
        $commandes = $event->getData();

        // Check if the form is based on the Commande entity and the materiel field exists
        if ($commandes instanceof Commande && $form->has('materiel')) {
            $materiels = $commandes->getMateriel();
            $extraFields = [];

            // Generate extra fields for each selected materiel
            foreach ($materiels as $materiel) {
                $extraFields[] = $this->createExtraField($materiel);
            }

            // Add the extra fields to the form
            $form->add('extraFields', CollectionType::class, [
                'entry_type' => ExtraFieldType::class, // Replace with your extra field type class
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype_name' => '__extraFieldName__',
                'mapped' => false,
                'data' => $extraFields,
            ]);
        }
    }

    private function createExtraField($materiel)
{
    $extraFieldName = 'extraField_' . $materiel->getId(); // Unique field name for each materiel

    return $this->createFormBuilder()
        ->add($extraFieldName, TextType::class, [
            'label' => $materiel->getNom(), // Use the materiel name as the label
            // Add any additional options or constraints for the extra field
        ])
        ->getForm();
}


    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }
}
