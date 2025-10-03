<?php

namespace App\Form;

use App\Entity\Groupe;
use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Visite du Château de Nantes',
                    'maxlength' => 100
                ],
                'help' => 'Donnez un nom attractif à votre sortie (max 100 caractères)',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom de la sortie est obligatoire'
                    ])
                ]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de début',
                'required' => true,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->format('Y-m-d\TH:i')
                ],
                'help' => 'Sélectionnez la date et l\'heure de début de votre sortie',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date de début est obligatoire'
                    ]),
                    new GreaterThan([
                        'value' => 'now',
                        'message' => 'La date de début doit être dans le futur'
                    ])
                ]
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => 'Date limite d\'inscription',
                'required' => true,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'max' => (new \DateTime('+1 year'))->format('Y-m-d\TH:i')
                ],
                'help' => 'Date limite pour que les participants puissent s\'inscrire (doit être antérieure à la date de début)',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date limite d\'inscription est obligatoire'
                    ]),
                    new LessThan([
                        'propertyPath' => 'parent.all[dateHeureDebut].data',
                        'message' => 'La date limite d\'inscription doit être antérieure à la date de début'
                    ])
                ]
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'label' => 'Nombre de places',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 100,
                    'placeholder' => 'Ex: 15'
                ],
                'help' => 'Nombre maximum de participants (entre 1 et 100)',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nombre de places est obligatoire'
                    ]),
                    new GreaterThanOrEqual([
                        'value' => 1,
                        'message' => 'Le nombre maximum d\'inscriptions doit être au moins 1'
                    ])
                ]
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 1440,
                    'placeholder' => 'Ex: 120 (optionnel)'
                ],
                'help' => 'Durée estimée de la sortie en minutes (optionnel, max 24h)'
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'maxlength' => 500,
                    'placeholder' => 'Décrivez votre sortie, les équipements nécessaires, le niveau requis...'
                ],
                'help' => 'Informations complémentaires sur la sortie (max 500 caractères)'
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => function (Lieu $lieu) {
                    return $lieu->getNom() . ' - ' . $lieu->getVille()->getNom() . ' (' . $lieu->getVille()->getCodePostal() . ')';
                },
                'label' => 'Lieu',
                'required' => true,
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Sélectionnez le lieu où se déroulera la sortie',
                'placeholder' => 'Choisissez un lieu...',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le lieu est obligatoire'
                    ])
                ]
            ])
            ->add('urlPhoto', UrlType::class, [
                'label' => 'URL de la photo',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=400&fit=crop',
                    'pattern' => '^https?://.+\.(jpg|jpeg|png|gif|webp)(\?.*)?$',
                    'title' => 'URL doit pointer vers une image (jpg, jpeg, png, gif, webp)'
                ],
                'help' => 'Lien vers une image représentative de votre sortie (optionnel). Formats acceptés: JPG, PNG, GIF, WebP',
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Regex([
                        'pattern' => '/^https?:\/\/.+\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i',
                        'message' => 'L\'URL doit pointer vers une image valide (JPG, PNG, GIF, WebP)',
                        'match' => true
                    ])
                ]
            ])
            ->add('groupe', EntityType::class, [
                'class' => Groupe::class,
                'choice_label' => 'nom',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'help' => 'Sélectionnez un groupe pour restreindre cette sortie aux membres du groupe (optionnel)',
                'placeholder' => 'Sortie publique (aucun groupe)',
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('g')
                        ->andWhere('g.actif = :actif')
                        ->setParameter('actif', true)
                        ->orderBy('g.nom', 'ASC');
                }
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
