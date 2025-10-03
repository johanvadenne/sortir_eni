<?php

namespace App\DataFixtures;

use App\Entity\Groupe;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class GroupeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Récupérer les participants existants
        $participants = $manager->getRepository(Participant::class)->findAll();

        if (empty($participants)) {
            return; // Pas de participants, pas de groupes
        }

        // Groupe de randonnée
        $groupeRandonnee = new Groupe();
        $groupeRandonnee->setNom('Randonneurs de Nantes');
        $groupeRandonnee->setDescription('Groupe de passionnés de randonnée qui organisent des sorties dans la région nantaise. Tous niveaux acceptés, de la balade familiale aux randonnées sportives.');
        $groupeRandonnee->setActif(true);
        $groupeRandonnee->setCreateur($participants[0]); // Premier participant comme créateur
        $groupeRandonnee->addParticipant($participants[0]);

        // Ajouter quelques participants au groupe de randonnée
        for ($i = 1; $i <= min(3, count($participants) - 1); $i++) {
            $groupeRandonnee->addParticipant($participants[$i]);
        }

        $manager->persist($groupeRandonnee);

        // Groupe de sport
        $groupeSport = new Groupe();
        $groupeSport->setNom('Sportifs ENI');
        $groupeSport->setDescription('Groupe pour les amateurs de sport : football, basketball, tennis, natation... Organisons des matchs et des entraînements ensemble !');
        $groupeSport->setActif(true);
        $groupeSport->setCreateur($participants[1] ?? $participants[0]);
        $groupeSport->addParticipant($participants[1] ?? $participants[0]);

        // Ajouter quelques participants au groupe sport
        for ($i = 2; $i <= min(4, count($participants) - 1); $i++) {
            $groupeSport->addParticipant($participants[$i]);
        }

        $manager->persist($groupeSport);

        // Groupe culturel
        $groupeCulturel = new Groupe();
        $groupeCulturel->setNom('Culture & Arts');
        $groupeCulturel->setDescription('Découvrons ensemble la culture nantaise : musées, théâtres, concerts, expositions. Partageons nos passions artistiques !');
        $groupeCulturel->setActif(true);
        $groupeCulturel->setCreateur($participants[2] ?? $participants[0]);
        $groupeCulturel->addParticipant($participants[2] ?? $participants[0]);

        // Ajouter quelques participants au groupe culturel
        for ($i = 3; $i <= min(5, count($participants) - 1); $i++) {
            $groupeCulturel->addParticipant($participants[$i]);
        }

        $manager->persist($groupeCulturel);

        // Groupe gastronomique
        $groupeGastronomique = new Groupe();
        $groupeGastronomique->setNom('Gastronomes Nantais');
        $groupeGastronomique->setDescription('Découvrons les meilleures tables de Nantes et de la région. Restaurants, bars, dégustations... Un groupe pour les gourmets !');
        $groupeGastronomique->setActif(true);
        $groupeGastronomique->setCreateur($participants[3] ?? $participants[0]);
        $groupeGastronomique->addParticipant($participants[3] ?? $participants[0]);

        // Ajouter quelques participants au groupe gastronomique
        for ($i = 4; $i <= min(6, count($participants) - 1); $i++) {
            $groupeGastronomique->addParticipant($participants[$i]);
        }

        $manager->persist($groupeGastronomique);

        // Groupe de jeux
        $groupeJeux = new Groupe();
        $groupeJeux->setNom('Gamers & Joueurs');
        $groupeJeux->setDescription('Soirées jeux de société, LAN parties, escape games... Rejoignez-nous pour des moments de détente et de convivialité !');
        $groupeJeux->setActif(true);
        $groupeJeux->setCreateur($participants[4] ?? $participants[0]);
        $groupeJeux->addParticipant($participants[4] ?? $participants[0]);

        // Ajouter quelques participants au groupe jeux
        for ($i = 5; $i <= min(7, count($participants) - 1); $i++) {
            $groupeJeux->addParticipant($participants[$i]);
        }

        $manager->persist($groupeJeux);

        // Groupe de voyage
        $groupeVoyage = new Groupe();
        $groupeVoyage->setNom('Voyageurs ENI');
        $groupeVoyage->setDescription('Organisons des voyages et des escapades ensemble ! Week-ends, vacances, découvertes de nouvelles destinations...');
        $groupeVoyage->setActif(true);
        $groupeVoyage->setCreateur($participants[5] ?? $participants[0]);
        $groupeVoyage->addParticipant($participants[5] ?? $participants[0]);

        // Ajouter quelques participants au groupe voyage
        for ($i = 6; $i <= min(8, count($participants) - 1); $i++) {
            $groupeVoyage->addParticipant($participants[$i]);
        }

        $manager->persist($groupeVoyage);

        // Groupe de développement
        $groupeDev = new Groupe();
        $groupeDev->setNom('Développeurs ENI');
        $groupeDev->setDescription('Groupe pour les passionnés de développement : hackathons, projets collaboratifs, partage de connaissances techniques...');
        $groupeDev->setActif(true);
        $groupeDev->setCreateur($participants[6] ?? $participants[0]);
        $groupeDev->addParticipant($participants[6] ?? $participants[0]);

        // Ajouter quelques participants au groupe dev
        for ($i = 7; $i <= min(9, count($participants) - 1); $i++) {
            $groupeDev->addParticipant($participants[$i]);
        }

        $manager->persist($groupeDev);

        // Groupe de musique
        $groupeMusique = new Groupe();
        $groupeMusique->setNom('Musiciens ENI');
        $groupeMusique->setDescription('Musiciens amateurs et professionnels, rejoignez-nous ! Jam sessions, concerts, découverte de nouveaux artistes...');
        $groupeMusique->setActif(true);
        $groupeMusique->setCreateur($participants[7] ?? $participants[0]);
        $groupeMusique->addParticipant($participants[7] ?? $participants[0]);

        // Ajouter quelques participants au groupe musique
        for ($i = 8; $i <= min(10, count($participants) - 1); $i++) {
            $groupeMusique->addParticipant($participants[$i]);
        }

        $manager->persist($groupeMusique);

        // Groupe de cinéma
        $groupeCinema = new Groupe();
        $groupeCinema->setNom('Cinéphiles ENI');
        $groupeCinema->setDescription('Sorties cinéma, festivals, discussions sur les films... Partageons notre passion pour le 7ème art !');
        $groupeCinema->setActif(true);
        $groupeCinema->setCreateur($participants[8] ?? $participants[0]);
        $groupeCinema->addParticipant($participants[8] ?? $participants[0]);

        // Ajouter quelques participants au groupe cinéma
        for ($i = 9; $i <= min(11, count($participants) - 1); $i++) {
            $groupeCinema->addParticipant($participants[$i]);
        }

        $manager->persist($groupeCinema);

        // Groupe de lecture
        $groupeLecture = new Groupe();
        $groupeLecture->setNom('Club de Lecture');
        $groupeLecture->setDescription('Échangeons nos lectures, organisons des clubs de lecture, découvrons de nouveaux auteurs... Pour tous les amoureux des livres !');
        $groupeLecture->setActif(true);
        $groupeLecture->setCreateur($participants[9] ?? $participants[0]);
        $groupeLecture->addParticipant($participants[9] ?? $participants[0]);

        // Ajouter quelques participants au groupe lecture
        for ($i = 10; $i <= min(12, count($participants) - 1); $i++) {
            $groupeLecture->addParticipant($participants[$i]);
        }

        $manager->persist($groupeLecture);

        // Groupe inactif (pour tester)
        $groupeInactif = new Groupe();
        $groupeInactif->setNom('Groupe Inactif');
        $groupeInactif->setDescription('Ce groupe est inactif pour tester les fonctionnalités.');
        $groupeInactif->setActif(false);
        $groupeInactif->setCreateur($participants[0]);
        $groupeInactif->addParticipant($participants[0]);

        $manager->persist($groupeInactif);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ParticipantFixtures::class,
        ];
    }
}
