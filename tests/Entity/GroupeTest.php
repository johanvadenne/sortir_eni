<?php

namespace App\Tests\Entity;

use App\Entity\Groupe;
use App\Entity\Participant;
use App\Entity\Sortie;
use PHPUnit\Framework\TestCase;

class GroupeTest extends TestCase
{
    public function testGroupeCreation(): void
    {
        $groupe = new Groupe();
        $groupe->setNom('Test Group');
        $groupe->setDescription('Description du groupe de test');
        $groupe->setActif(true);

        $this->assertEquals('Test Group', $groupe->getNom());
        $this->assertEquals('Description du groupe de test', $groupe->getDescription());
        $this->assertTrue($groupe->isActif());
        $this->assertInstanceOf(\DateTimeImmutable::class, $groupe->getDateCreation());
    }

    public function testGroupeWithParticipants(): void
    {
        $groupe = new Groupe();
        $groupe->setNom('Test Group');

        $participant1 = new Participant();
        $participant1->setPseudo('user1');
        $participant1->setNom('User');
        $participant1->setPrenom('One');
        $participant1->setMail('user1@test.com');

        $participant2 = new Participant();
        $participant2->setPseudo('user2');
        $participant2->setNom('User');
        $participant2->setPrenom('Two');
        $participant2->setMail('user2@test.com');

        $groupe->addParticipant($participant1);
        $groupe->addParticipant($participant2);

        $this->assertCount(2, $groupe->getParticipants());
        $this->assertTrue($groupe->isMembre($participant1));
        $this->assertTrue($groupe->isMembre($participant2));
        $this->assertEquals(2, $groupe->getNbParticipants());
    }

    public function testGroupeWithSorties(): void
    {
        $groupe = new Groupe();
        $groupe->setNom('Test Group');

        $sortie1 = new Sortie();
        $sortie1->setNom('Sortie 1');

        $sortie2 = new Sortie();
        $sortie2->setNom('Sortie 2');

        $groupe->addSorty($sortie1);
        $groupe->addSorty($sortie2);

        $this->assertCount(2, $groupe->getSorties());
        $this->assertEquals(2, $groupe->getNbSorties());
        $this->assertEquals($groupe, $sortie1->getGroupe());
        $this->assertEquals($groupe, $sortie2->getGroupe());
    }

    public function testGroupePermissions(): void
    {
        $createur = new Participant();
        $createur->setPseudo('createur');
        $createur->setNom('Createur');
        $createur->setPrenom('Test');
        $createur->setMail('createur@test.com');

        $membre = new Participant();
        $membre->setPseudo('membre');
        $membre->setNom('Membre');
        $membre->setPrenom('Test');
        $membre->setMail('membre@test.com');

        $nonMembre = new Participant();
        $nonMembre->setPseudo('nonmembre');
        $nonMembre->setNom('NonMembre');
        $nonMembre->setPrenom('Test');
        $nonMembre->setMail('nonmembre@test.com');

        $groupe = new Groupe();
        $groupe->setNom('Test Group');
        $groupe->setCreateur($createur);
        $groupe->addParticipant($createur);
        $groupe->addParticipant($membre);

        // Test des permissions
        $this->assertTrue($groupe->canGérer($createur));
        $this->assertFalse($groupe->canGérer($membre));
        $this->assertFalse($groupe->canGérer($nonMembre));

        $this->assertTrue($groupe->isMembre($createur));
        $this->assertTrue($groupe->isMembre($membre));
        $this->assertFalse($groupe->isMembre($nonMembre));
    }

    public function testGroupeToString(): void
    {
        $groupe = new Groupe();
        $groupe->setNom('Test Group');

        $this->assertEquals('Test Group', (string) $groupe);
    }

    public function testGroupeRemoveParticipant(): void
    {
        $groupe = new Groupe();
        $groupe->setNom('Test Group');

        $participant = new Participant();
        $participant->setPseudo('user1');
        $participant->setNom('User');
        $participant->setPrenom('One');
        $participant->setMail('user1@test.com');

        $groupe->addParticipant($participant);
        $this->assertCount(1, $groupe->getParticipants());

        $groupe->removeParticipant($participant);
        $this->assertCount(0, $groupe->getParticipants());
        $this->assertFalse($groupe->isMembre($participant));
    }

    public function testGroupeRemoveSortie(): void
    {
        $groupe = new Groupe();
        $groupe->setNom('Test Group');

        $sortie = new Sortie();
        $sortie->setNom('Test Sortie');

        $groupe->addSorty($sortie);
        $this->assertCount(1, $groupe->getSorties());

        $groupe->removeSorty($sortie);
        $this->assertCount(0, $groupe->getSorties());
        $this->assertNull($sortie->getGroupe());
    }
}
