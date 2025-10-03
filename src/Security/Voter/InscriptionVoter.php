<?php

namespace App\Security\Voter;

use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class InscriptionVoter extends Voter
{
    public const INSCRIRE = 'INSCRIRE';
    public const SE_DESINSCRIRE = 'SE_DESINSCRIRE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::INSCRIRE, self::SE_DESINSCRIRE])
            && ($subject instanceof Sortie || $subject instanceof Inscription);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof Participant) {
            return false;
        }

        return match ($attribute) {
            self::INSCRIRE => $this->canInscrire($subject, $user),
            self::SE_DESINSCRIRE => $this->canSeDesinscrire($subject, $user),
            default => false,
        };
    }

    private function canInscrire(Sortie $sortie, Participant $participant): bool
    {
        // Règle 1: L'état doit être "Ouverte"
        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            return false;
        }

        // Règle 2: Le nombre d'inscriptions actuelles doit être inférieur au maximum
        if ($sortie->isComplete()) {
            return false;
        }

        // Règle 3: La date limite d'inscription ne doit pas être dépassée
        if (!$sortie->isInscriptionOuverte()) {
            return false;
        }

        // Règle 4: Le participant ne doit pas déjà être inscrit
        foreach ($sortie->getInscriptions() as $inscription) {
            if ($inscription->getParticipant() === $participant) {
                return false;
            }
        }

        return true;
    }

    private function canSeDesinscrire(Inscription $inscription, Participant $participant): bool
    {
        // Vérifier que c'est bien l'inscription du participant
        if ($inscription->getParticipant() !== $participant) {
            return false;
        }

        $sortie = $inscription->getSortie();

        // Règle: Le désistement est interdit si la date limite d'inscription est dépassée
        if ($sortie->getDateLimiteInscription() < new \DateTime()) {
            return false;
        }

        return true;
    }
}
