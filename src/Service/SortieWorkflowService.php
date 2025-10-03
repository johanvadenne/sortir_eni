<?php

namespace App\Service;

use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Workflow\Exception\TransitionException;

class SortieWorkflowService
{
    public function __construct(
        private WorkflowInterface $sortieWorkflow,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Publie une sortie (cree → ouverte)
     */
    public function publierSortie(Sortie $sortie): bool
    {
        try {
            if ($this->sortieWorkflow->can($sortie, 'publier')) {
                $this->sortieWorkflow->apply($sortie, 'publier');
                $this->entityManager->flush();
                return true;
            }
        } catch (TransitionException $e) {
            // Transition non autorisée
        }

        return false;
    }

    /**
     * Clôture automatiquement les inscriptions (ouverte → cloturee)
     */
    public function cloreInscriptions(Sortie $sortie): bool
    {
        try {
            if ($this->sortieWorkflow->can($sortie, 'clore_auto')) {
                $this->sortieWorkflow->apply($sortie, 'clore_auto');
                $this->entityManager->flush();
                return true;
            }
        } catch (TransitionException $e) {
            // Transition non autorisée
        }

        return false;
    }

    /**
     * Lance la sortie (cloturee → en_cours)
     */
    public function lancerSortie(Sortie $sortie): bool
    {
        try {
            if ($this->sortieWorkflow->can($sortie, 'lancer')) {
                $this->sortieWorkflow->apply($sortie, 'lancer');
                $this->entityManager->flush();
                return true;
            }
        } catch (TransitionException $e) {
            // Transition non autorisée
        }

        return false;
    }

    /**
     * Termine la sortie (en_cours → terminee)
     */
    public function terminerSortie(Sortie $sortie): bool
    {
        try {
            if ($this->sortieWorkflow->can($sortie, 'terminer')) {
                $this->sortieWorkflow->apply($sortie, 'terminer');
                $this->entityManager->flush();
                return true;
            }
        } catch (TransitionException $e) {
            // Transition non autorisée
        }

        return false;
    }

    /**
     * Annule une sortie (ouverte|cloturee → annulee)
     */
    public function annulerSortie(Sortie $sortie): bool
    {
        try {
            if ($this->sortieWorkflow->can($sortie, 'annuler')) {
                $this->sortieWorkflow->apply($sortie, 'annuler');
                $this->entityManager->flush();
                return true;
            }
        } catch (TransitionException $e) {
            // Transition non autorisée
        }

        return false;
    }

    /**
     * Archive une sortie (terminee → historisee)
     */
    public function archiverSortie(Sortie $sortie): bool
    {
        try {
            if ($this->sortieWorkflow->can($sortie, 'archiver')) {
                $this->sortieWorkflow->apply($sortie, 'archiver');
                $this->entityManager->flush();
                return true;
            }
        } catch (TransitionException $e) {
            // Transition non autorisée
        }

        return false;
    }

    /**
     * Vérifie si une transition est possible
     */
    public function canTransition(Sortie $sortie, string $transition): bool
    {
        return $this->sortieWorkflow->can($sortie, $transition);
    }

    /**
     * Retourne les transitions possibles pour une sortie
     */
    public function getEnabledTransitions(Sortie $sortie): array
    {
        return $this->sortieWorkflow->getEnabledTransitions($sortie);
    }

    /**
     * Retourne l'état actuel de la sortie
     */
    public function getCurrentState(Sortie $sortie): string
    {
        return $this->sortieWorkflow->getMarking($sortie)->getPlaces()[0] ?? 'unknown';
    }

    /**
     * Traite automatiquement les transitions possibles
     */
    public function processAutomaticTransitions(Sortie $sortie): array
    {
        $transitions = [];

        // Vérifier la clôture automatique
        if ($this->canTransition($sortie, 'clore_auto')) {
            if ($this->cloreInscriptions($sortie)) {
                $transitions[] = 'clore_auto';
            }
        }

        // Vérifier le lancement automatique
        if ($this->canTransition($sortie, 'lancer')) {
            if ($this->lancerSortie($sortie)) {
                $transitions[] = 'lancer';
            }
        }

        // Vérifier la fin automatique
        if ($this->canTransition($sortie, 'terminer')) {
            if ($this->terminerSortie($sortie)) {
                $transitions[] = 'terminer';
            }
        }

        // Vérifier l'archivage automatique
        if ($this->canTransition($sortie, 'archiver')) {
            if ($this->archiverSortie($sortie)) {
                $transitions[] = 'archiver';
            }
        }

        return $transitions;
    }
}
