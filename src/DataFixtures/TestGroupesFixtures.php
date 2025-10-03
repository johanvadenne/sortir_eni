<?php

namespace App\DataFixtures;

use App\Entity\Groupe;
use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestGroupesFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Créer un site de test
        $site = new Site();
        $site->setNom('ENI Nantes');
        $manager->persist($site);

        // Créer quelques participants de test
        $participants = [];
        for ($i = 1; $i <= 5; $i++) {
            $participant = new Participant();
            $participant->setPseudo("user{$i}");
            $participant->setNom("User{$i}");
            $participant->setPrenom("Test{$i}");
            $participant->setMail("user{$i}@test.com");
            $participant->setTelephone("012345678{$i}");
            $participant->setSite($site);
            $participant->setActif(true);
            $participant->setAdministrateur($i === 1); // Premier utilisateur est admin

            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($participant, 'password');
            $participant->setMotDePasse($hashedPassword);

            $manager->persist($participant);
            $participants[] = $participant;
        }

        // Créer quelques groupes de test
        $groupesData = [
            [
                'nom' => 'Randonneurs de Nantes',
                'description' => 'Groupe de passionnés de randonnée qui organisent des sorties dans la région nantaise.',
                'createur' => 0,
                'membres' => [0, 1, 2]
            ],
            [
                'nom' => 'Sportifs ENI',
                'description' => 'Groupe pour les amateurs de sport : football, basketball, tennis, natation...',
                'createur' => 1,
                'membres' => [1, 2, 3]
            ],
            [
                'nom' => 'Culture & Arts',
                'description' => 'Découvrons ensemble la culture nantaise : musées, théâtres, concerts, expositions.',
                'createur' => 2,
                'membres' => [2, 3, 4]
            ]
        ];

        foreach ($groupesData as $groupeData) {
            $groupe = new Groupe();
            $groupe->setNom($groupeData['nom']);
            $groupe->setDescription($groupeData['description']);
            $groupe->setActif(true);

            // Récupérer le créateur
            $createur = $participants[$groupeData['createur']];
            $groupe->setCreateur($createur);
            $groupe->addParticipant($createur);

            // Ajouter les membres
            foreach ($groupeData['membres'] as $membreIndex) {
                if ($membreIndex !== $groupeData['createur']) {
                    $membre = $participants[$membreIndex];
                    $groupe->addParticipant($membre);
                }
            }

            $manager->persist($groupe);
        }

        $manager->flush();
    }
}
