<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Inscription;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/test-business-rules')]
#[IsGranted('ROLE_ADMIN')]
class TestBusinessRulesController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'test_business_rules')]
    public function index(): Response
    {
        $results = [];

        // Test 1: Validation des dates
        $results['date_validation'] = $this->testDateValidation();

        // Test 2: Règles d'inscription
        $results['inscription_rules'] = $this->testInscriptionRules();

        // Test 3: Règles d'annulation
        $results['annulation_rules'] = $this->testAnnulationRules();

        // Test 4: Règles de suppression
        $results['suppression_rules'] = $this->testSuppressionRules();

        return $this->render('test_business_rules/index.html.twig', [
            'results' => $results
        ]);
    }

    private function testDateValidation(): array
    {
        $results = [];

        // Test dateHeureDebut > now
        $sortie = new Sortie();
        $sortie->setNom('Test Date Passée');
        $sortie->setDateHeureDebut(new \DateTime('-1 day')); // Date dans le passé
        $sortie->setDateLimiteInscription(new \DateTime('-2 days'));
        $sortie->setNbInscriptionsMax(10);

        $errors = $this->validateEntity($sortie);
        $results['date_passee'] = [
            'test' => 'Date de début dans le passé',
            'valid' => empty($errors),
            'errors' => $errors
        ];

        // Test dateLimiteInscription < dateHeureDebut
        $sortie2 = new Sortie();
        $sortie2->setNom('Test Date Limite');
        $sortie2->setDateHeureDebut(new \DateTime('+1 day'));
        $sortie2->setDateLimiteInscription(new \DateTime('+2 days')); // Après la date de début
        $sortie2->setNbInscriptionsMax(10);

        $errors = $this->validateEntity($sortie2);
        $results['date_limite_apres_debut'] = [
            'test' => 'Date limite après date de début',
            'valid' => empty($errors),
            'errors' => $errors
        ];

        // Test nbInscriptionsMax >= 1
        $sortie3 = new Sortie();
        $sortie3->setNom('Test Nb Max');
        $sortie3->setDateHeureDebut(new \DateTime('+1 day'));
        $sortie3->setDateLimiteInscription(new \DateTime('+12 hours'));
        $sortie3->setNbInscriptionsMax(0); // Nombre invalide

        $errors = $this->validateEntity($sortie3);
        $results['nb_max_invalide'] = [
            'test' => 'Nombre max d\'inscriptions = 0',
            'valid' => empty($errors),
            'errors' => $errors
        ];

        return $results;
    }

    private function testInscriptionRules(): array
    {
        $results = [];

        // Créer une sortie en état "Ouverte"
        $sortie = $this->createTestSortie('Ouverte');
        $participant = $this->getTestParticipant();

        // Test inscription autorisée
        $results['inscription_autorisee'] = [
            'test' => 'Inscription autorisée (état Ouverte)',
            'authorized' => $this->isGranted('INSCRIRE', $sortie)
        ];

        // Test inscription interdite (état Clôturée)
        $sortieCloturee = $this->createTestSortie('Clôturée');
        $results['inscription_interdite_etat'] = [
            'test' => 'Inscription interdite (état Clôturée)',
            'authorized' => $this->isGranted('INSCRIRE', $sortieCloturee)
        ];

        return $results;
    }

    private function testAnnulationRules(): array
    {
        $results = [];

        // Test annulation autorisée (état Ouverte)
        $sortie = $this->createTestSortie('Ouverte');
        $results['annulation_autorisee'] = [
            'test' => 'Annulation autorisée (état Ouverte)',
            'authorized' => $this->isGranted('ANNULER', $sortie)
        ];

        // Test annulation interdite (état Créée)
        $sortieCreee = $this->createTestSortie('Créée');
        $results['annulation_interdite_creee'] = [
            'test' => 'Annulation interdite (état Créée)',
            'authorized' => $this->isGranted('ANNULER', $sortieCreee)
        ];

        return $results;
    }

    private function testSuppressionRules(): array
    {
        $results = [];

        // Test suppression autorisée (état Créée)
        $sortie = $this->createTestSortie('Créée');
        $results['suppression_autorisee'] = [
            'test' => 'Suppression autorisée (état Créée)',
            'authorized' => $this->isGranted('SUPPRIMER', $sortie)
        ];

        // Test suppression interdite (état Ouverte)
        $sortieOuverte = $this->createTestSortie('Ouverte');
        $results['suppression_interdite'] = [
            'test' => 'Suppression interdite (état Ouverte)',
            'authorized' => $this->isGranted('SUPPRIMER', $sortieOuverte)
        ];

        return $results;
    }

    private function createTestSortie(string $etatLibelle): Sortie
    {
        $etat = $this->entityManager->getRepository(Etat::class)
            ->findOneBy(['libelle' => $etatLibelle]);

        if (!$etat) {
            throw new \Exception("État '$etatLibelle' non trouvé");
        }

        $sortie = new Sortie();
        $sortie->setNom('Test Sortie');
        $sortie->setDateHeureDebut(new \DateTime('+1 day'));
        $sortie->setDateLimiteInscription(new \DateTime('+12 hours'));
        $sortie->setNbInscriptionsMax(10);
        $sortie->setEtat($etat);

        // Utiliser les entités existantes
        $site = $this->entityManager->getRepository(Site::class)->findOneBy([]);
        $ville = $this->entityManager->getRepository(Ville::class)->findOneBy([]);
        $lieu = $this->entityManager->getRepository(Lieu::class)->findOneBy([]);
        $organisateur = $this->entityManager->getRepository(Participant::class)->findOneBy([]);

        if ($site && $ville && $lieu && $organisateur) {
            $sortie->setLieu($lieu);
            $sortie->setOrganisateur($organisateur);
        }

        return $sortie;
    }

    private function getTestParticipant(): Participant
    {
        return $this->entityManager->getRepository(Participant::class)->findOneBy([]);
    }

    private function validateEntity($entity): array
    {
        $validator = $this->container->get('validator');
        $errors = $validator->validate($entity);

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }

        return $errorMessages;
    }
}
