<?php

namespace App\Security\Voter;

use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SortiePolicyVoter extends Voter
{
    public const PUBLISH = 'PUBLISH';
    public const EDIT = 'EDIT';
    public const CANCEL = 'CANCEL';
    public const DELETE = 'DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::PUBLISH, self::EDIT, self::CANCEL, self::DELETE])
            && $subject instanceof Sortie;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Participant) {
            return false;
        }

        return match ($attribute) {
            self::PUBLISH => $this->canPublish($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::CANCEL => $this->canCancel($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            default => false,
        };
    }

    /**
     * Vérifie si l'utilisateur peut publier la sortie
     */
    private function canPublish(Sortie $sortie, Participant $participant): bool
    {
        // Seul l'organisateur ou un admin peut publier
        if ($sortie->getOrganisateur() !== $participant && !$participant->isAdministrateur()) {
            return false;
        }

        // La sortie doit être en état "Créée"
        return $sortie->getEtat()->getLibelle() === 'Créée';
    }

    /**
     * Vérifie si l'utilisateur peut modifier la sortie
     */
    private function canEdit(Sortie $sortie, Participant $participant): bool
    {
        // Seul l'organisateur ou un admin peut modifier
        if ($sortie->getOrganisateur() !== $participant && !$participant->isAdministrateur()) {
            return false;
        }

        $etatLibelle = $sortie->getEtat()->getLibelle();

        // Modification possible en état "Créée" ou "Ouverte"
        return in_array($etatLibelle, ['Créée', 'Ouverte']);
    }

    /**
     * Vérifie si l'utilisateur peut annuler la sortie
     */
    private function canCancel(Sortie $sortie, Participant $participant): bool
    {
        // Seul l'organisateur ou un admin peut annuler
        if ($sortie->getOrganisateur() !== $participant && !$participant->isAdministrateur()) {
            return false;
        }

        $etatLibelle = $sortie->getEtat()->getLibelle();
        $now = new \DateTime();

        // Annulation possible en état "Ouverte" ou "Clôturée"
        if (!in_array($etatLibelle, ['Ouverte', 'Clôturée'])) {
            return false;
        }

        // Annulation impossible si la sortie a déjà commencé
        if ($sortie->getDateHeureDebut() <= $now) {
            return false;
        }

        return true;
    }

    /**
     * Vérifie si l'utilisateur peut supprimer la sortie
     */
    private function canDelete(Sortie $sortie, Participant $participant): bool
    {
        // Seul l'organisateur ou un admin peut supprimer
        if ($sortie->getOrganisateur() !== $participant && !$participant->isAdministrateur()) {
            return false;
        }

        // Suppression possible uniquement en état "Créée" (avant publication)
        return $sortie->getEtat()->getLibelle() === 'Créée';
    }
}
