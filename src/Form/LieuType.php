<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du lieu',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Château des Ducs de Bretagne',
                    'maxlength' => 100
                ],
                'help' => 'Nom du lieu où se déroulera la sortie (max 100 caractères)',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom du lieu est obligatoire'
                    ])
                ]
            ])
            ->add('rue', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 4 Place Marc Elder',
                    'maxlength' => 255
                ],
                'help' => 'Adresse complète du lieu (optionnel, max 255 caractères)'
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'required' => false,
                'scale' => 6,
                'attr' => [
                    'class' => 'form-control',
                    'step' => '0.000001',
                    'min' => -90,
                    'max' => 90,
                    'placeholder' => 'Ex: 47.2184'
                ],
                'help' => 'Coordonnée GPS latitude (optionnel, entre -90 et 90)',
                'constraints' => [
                    new Range([
                        'min' => -90,
                        'max' => 90,
                        'notInRangeMessage' => 'La latitude doit être comprise entre -90 et 90'
                    ])
                ]
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'required' => false,
                'scale' => 6,
                'attr' => [
                    'class' => 'form-control',
                    'step' => '0.000001',
                    'min' => -180,
                    'max' => 180,
                    'placeholder' => 'Ex: -1.5536'
                ],
                'help' => 'Coordonnée GPS longitude (optionnel, entre -180 et 180)',
                'constraints' => [
                    new Range([
                        'min' => -180,
                        'max' => 180,
                        'notInRangeMessage' => 'La longitude doit être comprise entre -180 et 180'
                    ])
                ]
            ])
            ->add('ville', EntityType::class, [
                'class' => Ville::class,
                'choice_label' => function(Ville $ville) {
                    return $ville->getNom() . ' (' . $ville->getCodePostal() . ')';
                },
                'label' => 'Ville',
                'required' => true,
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Sélectionnez la ville où se trouve le lieu',
                'placeholder' => 'Choisissez une ville...',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La ville est obligatoire'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
