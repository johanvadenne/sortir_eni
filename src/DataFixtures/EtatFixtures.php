<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $etats = [
            'Créée',
            'Ouverte',
            'Clôturée',
            'En cours',
            'Terminée',
            'Annulée',
            'Historisée'
        ];

        foreach ($etats as $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);

            $manager->persist($etat);

            // Créer des références pour les autres fixtures
            $this->addReference('etat_' . strtolower(str_replace([' ', 'é', 'è'], ['_', 'e', 'e'], $libelle)), $etat);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['etat'];
    }
}
