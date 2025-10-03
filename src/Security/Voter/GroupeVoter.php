<?php

namespace App\Security\Voter;

use App\Entity\Groupe;
use App\Entity\Participant;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class GroupeVoter extends Voter
{
    public const VIEW = 'GROUPE_VIEW';
    public const EDIT = 'GROUPE_EDIT';
    public const DELETE = 'GROUPE_DELETE';
    public const MANAGE_MEMBERS = 'GROUPE_MANAGE_MEMBERS';
    public const ADD_MEMBER = 'GROUPE_ADD_MEMBER';
    public const REMOVE_MEMBER = 'GROUPE_REMOVE_MEMBER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::VIEW,
            self::EDIT,
            self::DELETE,
            self::MANAGE_MEMBERS,
            self::ADD_MEMBER,
            self::REMOVE_MEMBER,
        ]) && $subject instanceof Groupe;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Si l'utilisateur n'est pas connecté, refuser l'accès
        if (!$user instanceof Participant) {
            return false;
        }

        $groupe = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($groupe, $user),
            self::EDIT => $this->canEdit($groupe, $user),
            self::DELETE => $this->canDelete($groupe, $user),
            self::MANAGE_MEMBERS => $this->canManageMembers($groupe, $user),
            self::ADD_MEMBER => $this->canAddMember($groupe, $user),
            self::REMOVE_MEMBER => $this->canRemoveMember($groupe, $user),
            default => false,
        };
    }

    private function canView(Groupe $groupe, Participant $user): bool
    {
        // Les administrateurs peuvent voir tous les groupes
        if ($user->isAdministrateur()) {
            return true;
        }

        // Les membres du groupe peuvent voir le groupe
        return $groupe->isMembre($user);
    }

    private function canEdit(Groupe $groupe, Participant $user): bool
    {
        // Les administrateurs peuvent modifier tous les groupes
        if ($user->isAdministrateur()) {
            return true;
        }

        // Seul le créateur peut modifier le groupe
        return $groupe->getCreateur() === $user;
    }

    private function canDelete(Groupe $groupe, Participant $user): bool
    {
        // Les administrateurs peuvent supprimer tous les groupes
        if ($user->isAdministrateur()) {
            return true;
        }

        // Seul le créateur peut supprimer le groupe
        return $groupe->getCreateur() === $user;
    }

    private function canManageMembers(Groupe $groupe, Participant $user): bool
    {
        // Les administrateurs peuvent gérer les membres de tous les groupes
        if ($user->isAdministrateur()) {
            return true;
        }

        // Seul le créateur peut gérer les membres
        return $groupe->getCreateur() === $user;
    }

    private function canAddMember(Groupe $groupe, Participant $user): bool
    {
        // Les administrateurs peuvent ajouter des membres à tous les groupes
        if ($user->isAdministrateur()) {
            return true;
        }

        // Seul le créateur peut ajouter des membres
        return $groupe->getCreateur() === $user;
    }

    private function canRemoveMember(Groupe $groupe, Participant $user): bool
    {
        // Les administrateurs peuvent retirer des membres de tous les groupes
        if ($user->isAdministrateur()) {
            return true;
        }

        // Seul le créateur peut retirer des membres
        return $groupe->getCreateur() === $user;
    }
}
