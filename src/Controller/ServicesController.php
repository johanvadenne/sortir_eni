<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Service\InscriptionService;
use App\Service\SortieStateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/services')]
#[IsGranted('ROLE_ADMIN')]
class ServicesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SortieStateService $sortieStateService,
        private InscriptionService $inscriptionService
    ) {
    }

    #[Route('/', name: 'services_index')]
    public function index(): Response
    {
        $results = [];

        // Test 1: SortieStateService
        $results['sortie_state_service'] = $this->testSortieStateService();

        // Test 2: InscriptionService
        $results['inscription_service'] = $this->testInscriptionService();

        // Test 3: SortiePolicyVoter
        $results['sortie_policy_voter'] = $this->testSortiePolicyVoter();

        return $this->render('services/index.html.twig', [
            'results' => $results
        ]);
    }

    private function testSortieStateService(): array
    {
        $results = [];

        // Créer une sortie de test
        $sortie = $this->createTestSortie('Créée');

        // Test publication
        $results['publier'] = [
            'test' => 'Publication d\'une sortie',
            'initial_state' => $sortie->getEtat()->getLibelle(),
            'can_publish' => $this->sortieStateService->publierSortie($sortie),
            'final_state' => $sortie->getEtat()->getLibelle()
        ];

        // Test clôture
        $sortie2 = $this->createTestSortie('Ouverte');
        $sortie2->setDateLimiteInscription(new \DateTime('-1 hour')); // Date dépassée
        $this->entityManager->flush();

        $results['cloturer'] = [
            'test' => 'Clôture automatique',
            'initial_state' => $sortie2->getEtat()->getLibelle(),
            'can_cloture' => $this->sortieStateService->cloturerInscriptions($sortie2),
            'final_state' => $sortie2->getEtat()->getLibelle()
        ];

        return $results;
    }

    private function testInscriptionService(): array
    {
        $results = [];

        // Créer une sortie ouverte
        $sortie = $this->createTestSortie('Ouverte');
        $participant = $this->getTestParticipant();

        // Test inscription
        try {
            $inscription = $this->inscriptionService->inscrire($participant, $sortie);
            $results['inscrire'] = [
                'test' => 'Inscription d\'un participant',
                'success' => true,
                'inscription_id' => $inscription->getId(),
                'nb_inscriptions' => $this->inscriptionService->getNbInscriptions($sortie)
            ];
        } catch (\Exception $e) {
            $results['inscrire'] = [
                'test' => 'Inscription d\'un participant',
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        // Test désistement
        try {
            $success = $this->inscriptionService->desister($participant, $sortie);
            $results['desister'] = [
                'test' => 'Désistement d\'un participant',
                'success' => $success,
                'nb_inscriptions' => $this->inscriptionService->getNbInscriptions($sortie)
            ];
        } catch (\Exception $e) {
            $results['desister'] = [
                'test' => 'Désistement d\'un participant',
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        return $results;
    }

    private function testSortiePolicyVoter(): array
    {
        $results = [];

        // Créer une sortie créée
        $sortie = $this->createTestSortie('Créée');
        $organisateur = $sortie->getOrganisateur();

        // Test droits de l'organisateur
        $results['organisateur_publish'] = [
            'test' => 'Droit PUBLISH pour organisateur',
            'authorized' => $this->isGranted('PUBLISH', $sortie)
        ];

        $results['organisateur_edit'] = [
            'test' => 'Droit EDIT pour organisateur',
            'authorized' => $this->isGranted('EDIT', $sortie)
        ];

        $results['organisateur_delete'] = [
            'test' => 'Droit DELETE pour organisateur',
            'authorized' => $this->isGranted('DELETE', $sortie)
        ];

        // Test avec une sortie ouverte
        $sortieOuverte = $this->createTestSortie('Ouverte');
        $results['organisateur_cancel'] = [
            'test' => 'Droit CANCEL pour organisateur (sortie ouverte)',
            'authorized' => $this->isGranted('CANCEL', $sortieOuverte)
        ];

        return $results;
    }

    private function createTestSortie(string $etatLibelle): Sortie
    {
        $etat = $this->entityManager->getRepository(Etat::class)
            ->findOneBy(['libelle' => $etatLibelle]);

        $sortie = new Sortie();
        $sortie->setNom('Test Service - ' . $etatLibelle);
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

    private function getTestParticipant(): Participant
    {
        return $this->entityManager->getRepository(Participant::class)->findOneBy([]);
    }
}
