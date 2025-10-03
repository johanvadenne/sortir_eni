<?php

namespace App\Service;

use App\Entity\Sortie;
use App\Entity\Etat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Workflow\Exception\TransitionException;

class SortieStateService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WorkflowInterface $sortieWorkflow
    ) {
    }

    /**
     * Publie une sortie (Créée → Ouverte)
     */
    public function publierSortie(Sortie $sortie): bool
    {
        try {
            if ($this->sortieWorkflow->can($sortie, 'publier')) {
                $this->sortieWorkflow->apply($sortie, 'publier');
                $this->synchronizeEtat($sortie, 'Ouverte');
                $this->entityManager->flush();
                return true;
            }
        } catch (TransitionException $e) {
            // Transition non autorisée
        }

        return false;
    }

    /**
     * Clôture les inscriptions (Ouverte → Clôturée)
     */
    public function cloturerInscriptions(Sortie $sortie): bool
    {
        // Vérifier les conditions de clôture
        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            return false;
        }

        $now = new \DateTime();
        $shouldCloture = false;

        // Clôturer si le nombre maximum d'inscriptions est atteint
        if ($sortie->getInscriptions()->count() >= $sortie->getNbInscriptionsMax()) {
            $shouldCloture = true;
        }
        // Clôturer si la date limite d'inscription est dépassée
        elseif ($sortie->getDateLimiteInscription() < $now) {
            $shouldCloture = true;
        }

        if (!$shouldCloture) {
            return false;
        }

        try {
            if ($this->sortieWorkflow->can($sortie, 'clore_auto')) {
                $this->sortieWorkflow->apply($sortie, 'clore_auto');
                $this->synchronizeEtat($sortie, 'Clôturée');
                $this->entityManager->flush();
                return true;
            }
        } catch (TransitionException $e) {
            // Transition non autorisée
        }

        return false;
    }

    /**
     * Démarre la sortie (Clôturée → Activité en cours)
     */
    public function demarrerSortie(Sortie $sortie): bool
    {
        // Vérifier les conditions de démarrage
        if ($sortie->getEtat()->getLibelle() !== 'Clôturée') {
            return false;
        }

        $now = new \DateTime();
        if ($sortie->getDateHeureDebut() > $now) {
            return false;
        }

        try {
            if ($this->sortieWorkflow->can($sortie, 'lancer')) {
                $this->sortieWorkflow->apply($sortie, 'lancer');
                $this->synchronizeEtat($sortie, 'En cours');
                $this->entityManager->flush();
                return true;
            }
        } catch (TransitionException $e) {
            // Transition non autorisée
        }

        return false;
    }

    /**
     * Termine la sortie (Activité en cours → Activité terminée)
     */
    public function terminerSortie(Sortie $sortie): bool
    {
        // Vérifier les conditions de terminaison
        if ($sortie->getEtat()->getLibelle() !== 'En cours') {
            return false;
        }

        $now = new \DateTime();
        $shouldTerminate = false;

        if ($sortie->getDuree() !== null) {
            $dateFinPrevue = (clone $sortie->getDateHeureDebut())->modify('+' . $sortie->getDuree() . ' minutes');
            if ($now >= $dateFinPrevue) {
                $shouldTerminate = true;
            }
        } else {
            // Si pas de durée définie, on ne termine pas automatiquement
            return false;
        }

        if (!$shouldTerminate) {
            return false;
        }

        try {
            if ($this->sortieWorkflow->can($sortie, 'terminer')) {
                $this->sortieWorkflow->apply($sortie, 'terminer');
                $this->synchronizeEtat($sortie, 'Terminée');
                $this->entityManager->flush();
                return true;
            }
        } catch (TransitionException $e) {
            // Transition non autorisée
        }

        return false;
    }

    /**
     * Annule une sortie
     */
    public function annulerSortie(Sortie $sortie): bool
    {
        // Vérifier les conditions d'annulation
        if (!in_array($sortie->getEtat()->getLibelle(), ['Ouverte', 'Clôturée'])) {
            return false;
        }

        $now = new \DateTime();
        if ($sortie->getDateHeureDebut() <= $now) {
            return false; // Ne peut pas annuler si la sortie a déjà commencé
        }

        try {
            if ($this->sortieWorkflow->can($sortie, 'annuler')) {
                $this->sortieWorkflow->apply($sortie, 'annuler');
                $this->synchronizeEtat($sortie, 'Annulée');
                $this->entityManager->flush();
                return true;
            }
        } catch (TransitionException $e) {
            // Transition non autorisée
        }

        return false;
    }

    /**
     * Historise une sortie (Activité terminée → Activité historisée)
     */
    public function historiserSortie(Sortie $sortie): bool
    {
        // Vérifier les conditions d'historisation
        if ($sortie->getEtat()->getLibelle() !== 'Terminée') {
            return false;
        }

        $now = new \DateTime();
        $dateArchivage = (clone $sortie->getDateHeureDebut())->modify('+1 month');

        if ($now < $dateArchivage) {
            return false; // Ne peut pas historiser avant 1 mois
        }

        try {
            if ($this->sortieWorkflow->can($sortie, 'archiver')) {
                $this->sortieWorkflow->apply($sortie, 'archiver');
                $this->synchronizeEtat($sortie, 'Historisée');
                $this->entityManager->flush();
                return true;
            }
        } catch (TransitionException $e) {
            // Transition non autorisée
        }

        return false;
    }

    /**
     * Vérifie si une sortie peut être automatiquement clôturée
     */
    public function shouldAutoCloturer(Sortie $sortie): bool
    {
        if ($sortie->getEtat()->getLibelle() !== 'Ouverte') {
            return false;
        }

        // Clôturer automatiquement si la date limite d'inscription est dépassée
        return $sortie->getDateLimiteInscription() < new \DateTime();
    }

    /**
     * Vérifie si une sortie peut être automatiquement démarrée
     */
    public function shouldAutoDemarrer(Sortie $sortie): bool
    {
        if ($sortie->getEtat()->getLibelle() !== 'Clôturée') {
            return false;
        }

        // Démarrer automatiquement si la date de début est atteinte
        return $sortie->getDateHeureDebut() <= new \DateTime();
    }

    /**
     * Synchronise l'état de la sortie avec l'entité Etat
     */
    private function synchronizeEtat(Sortie $sortie, string $etatLibelle): void
    {
        $etat = $this->entityManager->getRepository(Etat::class)
            ->findOneBy(['libelle' => $etatLibelle]);

        if ($etat) {
            $sortie->setEtat($etat);
        }
    }
}
