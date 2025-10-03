<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Service\SortieWorkflowService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/workflow')]
#[IsGranted('ROLE_ADMIN')]
class WorkflowController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SortieWorkflowService $workflowService
    ) {
    }

    #[Route('/', name: 'workflow_index')]
    public function index(): Response
    {
        $sorties = $this->entityManager->getRepository(Sortie::class)->findAll();

        $sortiesWithWorkflow = [];
        foreach ($sorties as $sortie) {
            $sortiesWithWorkflow[] = [
                'sortie' => $sortie,
                'currentState' => $this->workflowService->getCurrentState($sortie),
                'enabledTransitions' => $this->workflowService->getEnabledTransitions($sortie),
                'canPublier' => $this->workflowService->canTransition($sortie, 'publier'),
                'canCloreAuto' => $this->workflowService->canTransition($sortie, 'clore_auto'),
                'canLancer' => $this->workflowService->canTransition($sortie, 'lancer'),
                'canTerminer' => $this->workflowService->canTransition($sortie, 'terminer'),
                'canAnnuler' => $this->workflowService->canTransition($sortie, 'annuler'),
                'canArchiver' => $this->workflowService->canTransition($sortie, 'archiver'),
            ];
        }

        return $this->render('workflow/index.html.twig', [
            'sorties' => $sortiesWithWorkflow
        ]);
    }

    #[Route('/transition/{id}/{transition}', name: 'workflow_transition')]
    public function transition(int $id, string $transition): Response
    {
        $sortie = $this->entityManager->getRepository(Sortie::class)->find($id);

        if (!$sortie) {
            $this->addFlash('error', 'Sortie non trouvée');
            return $this->redirectToRoute('workflow_index');
        }

        $success = match ($transition) {
            'publier' => $this->workflowService->publierSortie($sortie),
            'clore_auto' => $this->workflowService->cloreInscriptions($sortie),
            'lancer' => $this->workflowService->lancerSortie($sortie),
            'terminer' => $this->workflowService->terminerSortie($sortie),
            'annuler' => $this->workflowService->annulerSortie($sortie),
            'archiver' => $this->workflowService->archiverSortie($sortie),
            default => false,
        };

        if ($success) {
            $this->addFlash('success', "Transition '$transition' appliquée avec succès");
        } else {
            $this->addFlash('error', "Transition '$transition' non autorisée");
        }

        return $this->redirectToRoute('workflow_index');
    }

    #[Route('/create-test-sortie', name: 'workflow_create_test')]
    public function createTestSortie(): Response
    {
        // Créer une sortie de test
        $sortie = new Sortie();
        $sortie->setNom('Sortie de test workflow');
        $sortie->setDateHeureDebut(new \DateTime('+2 days'));
        $sortie->setDateLimiteInscription(new \DateTime('+1 day'));
        $sortie->setNbInscriptionsMax(10);
        $sortie->setDuree(120); // 2 heures

        // Utiliser les entités existantes
        $etatCreee = $this->entityManager->getRepository(Etat::class)
            ->findOneBy(['libelle' => 'Créée']);
        $lieu = $this->entityManager->getRepository(Lieu::class)->findOneBy([]);
        $organisateur = $this->entityManager->getRepository(Participant::class)->findOneBy([]);

        if ($etatCreee && $lieu && $organisateur) {
            $sortie->setEtat($etatCreee);
            $sortie->setLieu($lieu);
            $sortie->setOrganisateur($organisateur);

            $this->entityManager->persist($sortie);
            $this->entityManager->flush();

            $this->addFlash('success', 'Sortie de test créée avec succès');
        } else {
            $this->addFlash('error', 'Impossible de créer la sortie de test - données manquantes');
        }

        return $this->redirectToRoute('workflow_index');
    }

    #[Route('/process-automatic', name: 'workflow_process_automatic')]
    public function processAutomatic(): Response
    {
        $sorties = $this->entityManager->getRepository(Sortie::class)->findAll();
        $totalTransitions = 0;

        foreach ($sorties as $sortie) {
            $transitions = $this->workflowService->processAutomaticTransitions($sortie);
            $totalTransitions += count($transitions);
        }

        $this->addFlash('success', "$totalTransitions transitions automatiques appliquées");

        return $this->redirectToRoute('workflow_index');
    }

    #[Route('/test-scenarios', name: 'workflow_test_scenarios')]
    public function testScenarios(): Response
    {
        $scenarios = [];

        // Scénario 1: Sortie créée → publiée
        $scenarios['publier'] = $this->testScenarioPublier();

        // Scénario 2: Clôture automatique
        $scenarios['clore_auto'] = $this->testScenarioCloreAuto();

        // Scénario 3: Annulation
        $scenarios['annuler'] = $this->testScenarioAnnuler();

        return $this->render('workflow/test_scenarios.html.twig', [
            'scenarios' => $scenarios
        ]);
    }

    private function testScenarioPublier(): array
    {
        $sortie = $this->createTestSortieForScenario('Créée');
        $results = [];

        $results['initial_state'] = $this->workflowService->getCurrentState($sortie);
        $results['can_publier'] = $this->workflowService->canTransition($sortie, 'publier');

        if ($results['can_publier']) {
            $this->workflowService->publierSortie($sortie);
            $results['final_state'] = $this->workflowService->getCurrentState($sortie);
        }

        return $results;
    }

    private function testScenarioCloreAuto(): array
    {
        $sortie = $this->createTestSortieForScenario('Ouverte');
        $results = [];

        // Simuler que la date limite est dépassée
        $sortie->setDateLimiteInscription(new \DateTime('-1 hour'));
        $this->entityManager->flush();

        $results['initial_state'] = $this->workflowService->getCurrentState($sortie);
        $results['can_clore_auto'] = $this->workflowService->canTransition($sortie, 'clore_auto');

        if ($results['can_clore_auto']) {
            $this->workflowService->cloreInscriptions($sortie);
            $results['final_state'] = $this->workflowService->getCurrentState($sortie);
        }

        return $results;
    }

    private function testScenarioAnnuler(): array
    {
        $sortie = $this->createTestSortieForScenario('Ouverte');
        $results = [];

        $results['initial_state'] = $this->workflowService->getCurrentState($sortie);
        $results['can_annuler'] = $this->workflowService->canTransition($sortie, 'annuler');

        if ($results['can_annuler']) {
            $this->workflowService->annulerSortie($sortie);
            $results['final_state'] = $this->workflowService->getCurrentState($sortie);
        }

        return $results;
    }

    private function createTestSortieForScenario(string $etatLibelle): Sortie
    {
        $etat = $this->entityManager->getRepository(Etat::class)
            ->findOneBy(['libelle' => $etatLibelle]);

        $sortie = new Sortie();
        $sortie->setNom('Test Scenario - ' . $etatLibelle);
        $sortie->setDateHeureDebut(new \DateTime('+2 days'));
        $sortie->setDateLimiteInscription(new \DateTime('+1 day'));
        $sortie->setNbInscriptionsMax(10);
        $sortie->setEtat($etat);

        $lieu = $this->entityManager->getRepository(Lieu::class)->findOneBy([]);
        $organisateur = $this->entityManager->getRepository(Participant::class)->findOneBy([]);

        if ($lieu && $organisateur) {
            $sortie->setLieu($lieu);
            $sortie->setOrganisateur($organisateur);
        }

        $this->entityManager->persist($sortie);
        $this->entityManager->flush();

        return $sortie;
    }
}
