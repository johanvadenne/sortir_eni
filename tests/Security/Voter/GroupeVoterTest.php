<?php

namespace App\Tests\Security\Voter;

use App\Entity\Groupe;
use App\Entity\Participant;
use App\Security\Voter\GroupeVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GroupeVoterTest extends TestCase
{
    private GroupeVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new GroupeVoter();
    }

    public function testSupports(): void
    {
        $groupe = new Groupe();
        $groupe->setNom('Test Group');

        $this->assertTrue($this->voter->supports(GroupeVoter::VIEW, $groupe));
        $this->assertTrue($this->voter->supports(GroupeVoter::EDIT, $groupe));
        $this->assertTrue($this->voter->supports(GroupeVoter::DELETE, $groupe));
        $this->assertTrue($this->voter->supports(GroupeVoter::MANAGE_MEMBERS, $groupe));
        $this->assertTrue($this->voter->supports(GroupeVoter::ADD_MEMBER, $groupe));
        $this->assertTrue($this->voter->supports(GroupeVoter::REMOVE_MEMBER, $groupe));

        $this->assertFalse($this->voter->supports('INVALID_ATTRIBUTE', $groupe));
        $this->assertFalse($this->voter->supports(GroupeVoter::VIEW, new \stdClass()));
    }

    public function testViewPermission(): void
    {
        $createur = $this->createParticipant('createur', true);
        $membre = $this->createParticipant('membre', false);
        $nonMembre = $this->createParticipant('nonmembre', false);
        $admin = $this->createParticipant('admin', true);
        $admin->setAdministrateur(true);

        $groupe = new Groupe();
        $groupe->setNom('Test Group');
        $groupe->setCreateur($createur);
        $groupe->addParticipant($createur);
        $groupe->addParticipant($membre);

        // Le créateur peut voir le groupe
        $this->assertTrue($this->vote(GroupeVoter::VIEW, $groupe, $createur));

        // Un membre peut voir le groupe
        $this->assertTrue($this->vote(GroupeVoter::VIEW, $groupe, $membre));

        // Un non-membre ne peut pas voir le groupe
        $this->assertFalse($this->vote(GroupeVoter::VIEW, $groupe, $nonMembre));

        // Un admin peut voir tous les groupes
        $this->assertTrue($this->vote(GroupeVoter::VIEW, $groupe, $admin));
    }

    public function testEditPermission(): void
    {
        $createur = $this->createParticipant('createur', true);
        $membre = $this->createParticipant('membre', false);
        $admin = $this->createParticipant('admin', true);
        $admin->setAdministrateur(true);

        $groupe = new Groupe();
        $groupe->setNom('Test Group');
        $groupe->setCreateur($createur);
        $groupe->addParticipant($createur);
        $groupe->addParticipant($membre);

        // Seul le créateur peut modifier
        $this->assertTrue($this->vote(GroupeVoter::EDIT, $groupe, $createur));
        $this->assertFalse($this->vote(GroupeVoter::EDIT, $groupe, $membre));

        // Un admin peut modifier tous les groupes
        $this->assertTrue($this->vote(GroupeVoter::EDIT, $groupe, $admin));
    }

    public function testDeletePermission(): void
    {
        $createur = $this->createParticipant('createur', true);
        $membre = $this->createParticipant('membre', false);
        $admin = $this->createParticipant('admin', true);
        $admin->setAdministrateur(true);

        $groupe = new Groupe();
        $groupe->setNom('Test Group');
        $groupe->setCreateur($createur);
        $groupe->addParticipant($createur);
        $groupe->addParticipant($membre);

        // Seul le créateur peut supprimer
        $this->assertTrue($this->vote(GroupeVoter::DELETE, $groupe, $createur));
        $this->assertFalse($this->vote(GroupeVoter::DELETE, $groupe, $membre));

        // Un admin peut supprimer tous les groupes
        $this->assertTrue($this->vote(GroupeVoter::DELETE, $groupe, $admin));
    }

    public function testManageMembersPermission(): void
    {
        $createur = $this->createParticipant('createur', true);
        $membre = $this->createParticipant('membre', false);
        $admin = $this->createParticipant('admin', true);
        $admin->setAdministrateur(true);

        $groupe = new Groupe();
        $groupe->setNom('Test Group');
        $groupe->setCreateur($createur);
        $groupe->addParticipant($createur);
        $groupe->addParticipant($membre);

        // Seul le créateur peut gérer les membres
        $this->assertTrue($this->vote(GroupeVoter::MANAGE_MEMBERS, $groupe, $createur));
        $this->assertFalse($this->vote(GroupeVoter::MANAGE_MEMBERS, $groupe, $membre));

        // Un admin peut gérer les membres de tous les groupes
        $this->assertTrue($this->vote(GroupeVoter::MANAGE_MEMBERS, $groupe, $admin));
    }

    public function testAddMemberPermission(): void
    {
        $createur = $this->createParticipant('createur', true);
        $membre = $this->createParticipant('membre', false);
        $admin = $this->createParticipant('admin', true);
        $admin->setAdministrateur(true);

        $groupe = new Groupe();
        $groupe->setNom('Test Group');
        $groupe->setCreateur($createur);
        $groupe->addParticipant($createur);
        $groupe->addParticipant($membre);

        // Seul le créateur peut ajouter des membres
        $this->assertTrue($this->vote(GroupeVoter::ADD_MEMBER, $groupe, $createur));
        $this->assertFalse($this->vote(GroupeVoter::ADD_MEMBER, $groupe, $membre));

        // Un admin peut ajouter des membres à tous les groupes
        $this->assertTrue($this->vote(GroupeVoter::ADD_MEMBER, $groupe, $admin));
    }

    public function testRemoveMemberPermission(): void
    {
        $createur = $this->createParticipant('createur', true);
        $membre = $this->createParticipant('membre', false);
        $admin = $this->createParticipant('admin', true);
        $admin->setAdministrateur(true);

        $groupe = new Groupe();
        $groupe->setNom('Test Group');
        $groupe->setCreateur($createur);
        $groupe->addParticipant($createur);
        $groupe->addParticipant($membre);

        // Seul le créateur peut retirer des membres
        $this->assertTrue($this->vote(GroupeVoter::REMOVE_MEMBER, $groupe, $createur));
        $this->assertFalse($this->vote(GroupeVoter::REMOVE_MEMBER, $groupe, $membre));

        // Un admin peut retirer des membres de tous les groupes
        $this->assertTrue($this->vote(GroupeVoter::REMOVE_MEMBER, $groupe, $admin));
    }

    public function testVoteWithNonParticipant(): void
    {
        $groupe = new Groupe();
        $groupe->setNom('Test Group');

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(new \stdClass());

        $this->assertFalse($this->voter->vote($token, $groupe, [GroupeVoter::VIEW]));
    }

    private function createParticipant(string $pseudo, bool $actif = true): Participant
    {
        $participant = new Participant();
        $participant->setPseudo($pseudo);
        $participant->setNom('Test');
        $participant->setPrenom('User');
        $participant->setMail($pseudo . '@test.com');
        $participant->setActif($actif);
        return $participant;
    }

    private function vote(string $attribute, Groupe $groupe, Participant $user): bool
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($user);

        return $this->voter->vote($token, $groupe, [$attribute]);
    }
}
