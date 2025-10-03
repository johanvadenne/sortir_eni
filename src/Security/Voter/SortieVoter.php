<?php

namespace App\Security\Voter;

use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SortieVoter extends Voter
{
    public const ANNULER = 'ANNULER';
    public const SUPPRIMER = 'SUPPRIMER';
    public const MODIFIER = 'MODIFIER';
    public const VOIR = 'VOIR';
    public const S_INSCRIRE = 'S_INSCRIRE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ANNULER, self::SUPPRIMER, self::MODIFIER, self::VOIR, self::S_INSCRIRE])
            && $subject instanceof Sortie;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Participant) {
            return false;
        }

        return match ($attribute) {
            self::ANNULER => $this->canAnnuler($subject, $user),
            self::SUPPRIMER => $this->canSupprimer($subject, $user),
            self::MODIFIER => $this->canModifier($subject, $user),
            self::VOIR => $this->canVoir($subject, $user),
            self::S_INSCRIRE => $this->canSInscrire($subject, $user),
            default => false,
        };
    }

    private function canAnnuler(Sortie $sortie, Participant $participant): bool
    {
        // Seul l'organisateur ou un administrateur peut annuler
        if ($sortie->getOrganisateur() !== $participant && !$participant->isAdministrateur()) {
            return false;
        }

        $etatLibelle = $sortie->getEtat()->getLibelle();
        $now = new \DateTime();

        // Règle: Annulation possible à partir de "Ouverte" jusqu'à la réalisation (début) de la sortie
        if ($etatLibelle === 'Créée') {
            return false; // Pas encore publiée
        }

        if ($etatLibelle === 'Activité en cours' || $etatLibelle === 'Activité terminée' || $etatLibelle === 'Activité historisée') {
            return false; // Trop tard
        }

        // Si la sortie a commencé, on ne peut plus l'annuler
        if ($sortie->getDateHeureDebut() <= $now) {
            return false;
        }

        return true;
    }

    private function canSupprimer(Sortie $sortie, Participant $participant): bool
    {
        // Seul l'organisateur ou un administrateur peut supprimer
        if ($sortie->getOrganisateur() !== $participant && !$participant->isAdministrateur()) {
            return false;
        }

        // Règle: Suppression possible uniquement en état "Créée" (avant publication)
        return $sortie->getEtat()->getLibelle() === 'Créée';
    }

    private function canModifier(Sortie $sortie, Participant $participant): bool
    {
        // Seul l'organisateur ou un administrateur peut modifier
        if ($sortie->getOrganisateur() !== $participant && !$participant->isAdministrateur()) {
            return false;
        }

        $etatLibelle = $sortie->getEtat()->getLibelle();

        // Modification possible en état "Créée" ou "Ouverte"
        return in_array($etatLibelle, ['Créée', 'Ouverte']);
    }

    private function canVoir(Sortie $sortie, Participant $participant): bool
    {
        // Utiliser la méthode de l'entité Sortie pour vérifier la visibilité
        return $sortie->canVoir($participant);
    }

    private function canSInscrire(Sortie $sortie, Participant $participant): bool
    {
        // Utiliser la méthode de l'entité Sortie pour vérifier l'inscription
        return $sortie->canSInscrire($participant);
    }
}
