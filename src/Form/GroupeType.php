<?php

namespace App\Form;

use App\Entity\Groupe;
use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class GroupeType extends AbstractType
{
    public function __construct(
        private ParticipantRepository $participantRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du groupe',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Groupe de randonnée Nantes',
                    'maxlength' => 50,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom du groupe est obligatoire.',
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Le nom du groupe doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom du groupe ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'help' => 'Nom unique du groupe (3-50 caractères)',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Décrivez le groupe, ses objectifs, ses activités...',
                    'rows' => 4,
                    'maxlength' => 500,
                ],
                'constraints' => [
                    new Length([
                        'max' => 500,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
                'help' => 'Description optionnelle du groupe (max 500 caractères)',
            ])
            ->add('actif', CheckboxType::class, [
                'label' => 'Groupe actif',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'help' => 'Un groupe inactif n\'apparaîtra plus dans les listes',
            ]);

        // Ajouter le champ participants seulement si on est en mode édition
        if ($options['mode'] === 'edit') {
            $builder->add('participants', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => function (Participant $participant) {
                    return $participant->getPrenom() . ' ' . $participant->getNom() . ' (' . $participant->getPseudo() . ')';
                },
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-select',
                    'data-placeholder' => 'Sélectionnez les participants...',
                ],
                'query_builder' => function (ParticipantRepository $repository) {
                    return $repository->createQueryBuilder('p')
                        ->andWhere('p.actif = :actif')
                        ->setParameter('actif', true)
                        ->orderBy('p.nom', 'ASC')
                        ->addOrderBy('p.prenom', 'ASC');
                },
                'help' => 'Sélectionnez les participants à ajouter au groupe',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Groupe::class,
            'mode' => 'create', // 'create' ou 'edit'
        ]);

        $resolver->setAllowedValues('mode', ['create', 'edit']);
    }
}
