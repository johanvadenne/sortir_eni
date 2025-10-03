<?php

namespace App\Service;

use App\Entity\Inscription;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class InscriptionService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Inscrit un participant à une sortie
     */
    public function inscrire(Participant $participant, Sortie $sortie): Inscription
    {
        // Vérifications préalables
        $this->validateInscription($participant, $sortie);

        // Vérifier si déjà inscrit
        $existingInscription = $this->entityManager->getRepository(Inscription::class)
            ->findOneBy([
                'participant' => $participant,
                'sortie' => $sortie
            ]);

        if ($existingInscription) {
            throw new \InvalidArgumentException('Le participant est déjà inscrit à cette sortie');
        }

        // Créer l'inscription
        $inscription = new Inscription();
        $inscription->setParticipant($participant);
        $inscription->setSortie($sortie);
        $inscription->setDateInscription(new \DateTime());

        $this->entityManager->persist($inscription);
        $this->entityManager->flush();

        return $inscription;
    }

    /**
     * Désinscrit un participant d'une sortie
     */
    public function desister(Participant $participant, Sortie $sortie): bool
    {
        // Vérifications préalables
        $this->validateDesistement($participant, $sortie);

        // Trouver l'inscription
        $inscription = $this->entityManager->getRepository(Inscription::class)
            ->findOneBy([
                'participant' => $participant,
                'sortie' => $sortie
            ]);

        if (!$inscription) {
            throw new \InvalidArgumentException('Le participant n\'est pas inscrit à cette sortie');
        }

        // Supprimer l'inscription
        $this->entityManager->remove($inscription);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Valide les conditions d'inscription
     */
    private function validateInscription(Participant $participant, Sortie $sortie): void
    {
        // Vérifier l'état de la sortie
        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            throw new AccessDeniedException('Les inscriptions ne sont pas ouvertes pour cette sortie');
        }

        // Vérifier la capacité
        if ($sortie->isComplete()) {
            throw new AccessDeniedException('La sortie est complète');
        }

        // Vérifier la date limite
        if (!$sortie->isInscriptionOuverte()) {
            throw new AccessDeniedException('La date limite d\'inscription est dépassée');
        }

        // Vérifier que le participant est actif
        if (!$participant->isActif()) {
            throw new AccessDeniedException('Le participant n\'est pas actif');
        }
    }

    /**
     * Valide les conditions de désistement
     */
    private function validateDesistement(Participant $participant, Sortie $sortie): void
    {
        // Vérifier que le participant est bien inscrit
        $inscription = $this->entityManager->getRepository(Inscription::class)
            ->findOneBy([
                'participant' => $participant,
                'sortie' => $sortie
            ]);

        if (!$inscription) {
            throw new \InvalidArgumentException('Le participant n\'est pas inscrit à cette sortie');
        }

        // Vérifier la date limite de désistement
        if ($sortie->getDateLimiteInscription() < new \DateTime()) {
            throw new AccessDeniedException('Le désistement n\'est plus possible après la date limite d\'inscription');
        }

        // Vérifier que la sortie n'a pas encore commencé
        if ($sortie->getDateHeureDebut() <= new \DateTime()) {
            throw new AccessDeniedException('Le désistement n\'est plus possible après le début de la sortie');
        }
    }

    /**
     * Retourne le nombre d'inscriptions pour une sortie
     */
    public function getNbInscriptions(Sortie $sortie): int
    {
        return $this->entityManager->getRepository(Inscription::class)
            ->count(['sortie' => $sortie]);
    }

    /**
     * Retourne les participants inscrits à une sortie
     */
    public function getParticipantsInscrits(Sortie $sortie): array
    {
        $inscriptions = $this->entityManager->getRepository(Inscription::class)
            ->findBy(['sortie' => $sortie], ['dateInscription' => 'ASC']);

        return array_map(fn($inscription) => $inscription->getParticipant(), $inscriptions);
    }

    /**
     * Retourne les sorties auxquelles un participant est inscrit
     */
    public function getSortiesInscrites(Participant $participant): array
    {
        $inscriptions = $this->entityManager->getRepository(Inscription::class)
            ->findBy(['participant' => $participant], ['dateInscription' => 'DESC']);

        return array_map(fn($inscription) => $inscription->getSortie(), $inscriptions);
    }

    /**
     * Vérifie si un participant est inscrit à une sortie
     */
    public function isInscrit(Participant $participant, Sortie $sortie): bool
    {
        $inscription = $this->entityManager->getRepository(Inscription::class)
            ->findOneBy([
                'participant' => $participant,
                'sortie' => $sortie
            ]);

        return $inscription !== null;
    }

    /**
     * Annule toutes les inscriptions d'une sortie (en cas d'annulation de la sortie)
     */
    public function annulerToutesInscriptions(Sortie $sortie): int
    {
        $inscriptions = $this->entityManager->getRepository(Inscription::class)
            ->findBy(['sortie' => $sortie]);

        $count = count($inscriptions);

        foreach ($inscriptions as $inscription) {
            $this->entityManager->remove($inscription);
        }

        $this->entityManager->flush();

        return $count;
    }
}
