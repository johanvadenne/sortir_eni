<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class AnnulationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('motif', TextareaType::class, [
                'label' => 'Motif d\'annulation',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Ex: Mauvais temps, manque de participants, problème technique...'
                ],
                'constraints' => [
                    new Length([
                        'max' => 500,
                        'maxMessage' => 'Le motif ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Pas de data_class car c'est un formulaire simple
        ]);
    }
}
