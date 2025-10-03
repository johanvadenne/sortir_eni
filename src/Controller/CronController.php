<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Service\SortieStateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cron')]
#[IsGranted('ROLE_ADMIN')]
class CronController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SortieStateService $sortieStateService
    ) {
    }

    #[Route('/', name: 'cron_index')]
    public function index(): Response
    {
        $now = new \DateTime();

        // Analyser les sorties qui seraient affectées
        $analysis = $this->analyzeSorties();

        return $this->render('cron/index.html.twig', [
            'now' => $now,
            'analysis' => $analysis
        ]);
    }

    #[Route('/simulate', name: 'cron_simulate')]
    public function simulate(): Response
    {
        $results = [];
        $now = new \DateTime();

        // Simuler les transitions sans les appliquer
        $results['cloturee'] = $this->simulateOuverteToCloturee();
        $results['en_cours'] = $this->simulateClotureeToEnCours();
        $results['terminee'] = $this->simulateEnCoursToTerminee();
        $results['historisee'] = $this->simulateTermineeToHistorisee();

        return $this->render('cron/simulate.html.twig', [
            'now' => $now,
            'results' => $results
        ]);
    }

    #[Route('/execute', name: 'cron_execute')]
    public function execute(): Response
    {
        $results = [];
        $errors = [];

        // Exécuter les transitions
        $results['cloturee'] = $this->executeOuverteToCloturee($errors);
        $results['en_cours'] = $this->executeClotureeToEnCours($errors);
        $results['terminee'] = $this->executeEnCoursToTerminee($errors);
        $results['historisee'] = $this->executeTermineeToHistorisee($errors);

        $totalTransitions = array_sum($results);

        if ($totalTransitions > 0) {
            $this->addFlash('success', "$totalTransitions transitions automatiques exécutées");
        } else {
            $this->addFlash('info', 'Aucune transition automatique nécessaire');
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }
        }

        return $this->redirectToRoute('cron_index');
    }

    private function analyzeSorties(): array
    {
        $analysis = [
            'ouverte_to_cloturee' => [],
            'cloturee_to_en_cours' => [],
            'en_cours_to_terminee' => [],
            'terminee_to_historisee' => []
        ];

        $now = new \DateTime();

        // Analyser les sorties "Ouverte"
        $sortiesOuverte = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('e.libelle = :etat')
            ->setParameter('etat', 'Ouverte')
            ->getQuery()
            ->getResult();

        foreach ($sortiesOuverte as $sortie) {
            if ($sortie->isComplete() || $sortie->getDateLimiteInscription() < $now) {
                $analysis['ouverte_to_cloturee'][] = [
                    'sortie' => $sortie,
                    'reason' => $sortie->isComplete() ? 'Nombre max atteint' : 'Date limite dépassée'
                ];
            }
        }

        // Analyser les sorties "Clôturée"
        $sortiesCloturee = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('e.libelle = :etat')
            ->setParameter('etat', 'Clôturée')
            ->getQuery()
            ->getResult();

        foreach ($sortiesCloturee as $sortie) {
            if ($sortie->getDateHeureDebut() <= $now) {
                $analysis['cloturee_to_en_cours'][] = $sortie;
            }
        }

        // Analyser les sorties "Activité en cours"
        $sortiesEnCours = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('e.libelle = :etat')
            ->setParameter('etat', 'Activité en cours')
            ->getQuery()
            ->getResult();

        foreach ($sortiesEnCours as $sortie) {
            if ($sortie->getDuree() !== null) {
                $finSortie = clone $sortie->getDateHeureDebut();
                $finSortie->modify("+{$sortie->getDuree()} minutes");

                if ($now >= $finSortie) {
                    $analysis['en_cours_to_terminee'][] = $sortie;
                }
            }
        }

        // Analyser les sorties "Activité terminée"
        $sortiesTerminee = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('e.libelle = :etat')
            ->setParameter('etat', 'Activité terminée')
            ->getQuery()
            ->getResult();

        foreach ($sortiesTerminee as $sortie) {
            $dateArchivage = clone $sortie->getDateHeureDebut();
            $dateArchivage->modify('+1 month');

            if ($now >= $dateArchivage) {
                $analysis['terminee_to_historisee'][] = $sortie;
            }
        }

        return $analysis;
    }

    private function simulateOuverteToCloturee(): array
    {
        $sorties = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('e.libelle = :etat')
            ->setParameter('etat', 'Ouverte')
            ->getQuery()
            ->getResult();

        $results = [];
        foreach ($sorties as $sortie) {
            if ($sortie->isComplete() || $sortie->getDateLimiteInscription() < new \DateTime()) {
                $results[] = [
                    'sortie' => $sortie,
                    'reason' => $sortie->isComplete() ? 'Nombre max atteint' : 'Date limite dépassée'
                ];
            }
        }
        return $results;
    }

    private function simulateClotureeToEnCours(): array
    {
        $sorties = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('e.libelle = :etat')
            ->setParameter('etat', 'Clôturée')
            ->getQuery()
            ->getResult();

        $results = [];
        $now = new \DateTime();
        foreach ($sorties as $sortie) {
            if ($sortie->getDateHeureDebut() <= $now) {
                $results[] = $sortie;
            }
        }
        return $results;
    }

    private function simulateEnCoursToTerminee(): array
    {
        $sorties = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('e.libelle = :etat')
            ->setParameter('etat', 'Activité en cours')
            ->getQuery()
            ->getResult();

        $results = [];
        $now = new \DateTime();
        foreach ($sorties as $sortie) {
            if ($sortie->getDuree() !== null) {
                $finSortie = clone $sortie->getDateHeureDebut();
                $finSortie->modify("+{$sortie->getDuree()} minutes");

                if ($now >= $finSortie) {
                    $results[] = $sortie;
                }
            }
        }
        return $results;
    }

    private function simulateTermineeToHistorisee(): array
    {
        $sorties = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.etat', 'e')
            ->where('e.libelle = :etat')
            ->setParameter('etat', 'Activité terminée')
            ->getQuery()
            ->getResult();

        $results = [];
        $now = new \DateTime();
        foreach ($sorties as $sortie) {
            $dateArchivage = clone $sortie->getDateHeureDebut();
            $dateArchivage->modify('+1 month');

            if ($now >= $dateArchivage) {
                $results[] = $sortie;
            }
        }
        return $results;
    }

    private function executeOuverteToCloturee(array &$errors): int
    {
        $count = 0;
        $sorties = $this->simulateOuverteToCloturee();

        foreach ($sorties as $data) {
            try {
                if ($this->sortieStateService->cloturerInscriptions($data['sortie'])) {
                    $count++;
                }
            } catch (\Exception $e) {
                $errors[] = "Erreur clôture {$data['sortie']->getNom()}: " . $e->getMessage();
            }
        }

        return $count;
    }

    private function executeClotureeToEnCours(array &$errors): int
    {
        $count = 0;
        $sorties = $this->simulateClotureeToEnCours();

        foreach ($sorties as $sortie) {
            try {
                if ($this->sortieStateService->demarrerSortie($sortie)) {
                    $count++;
                }
            } catch (\Exception $e) {
                $errors[] = "Erreur démarrage {$sortie->getNom()}: " . $e->getMessage();
            }
        }

        return $count;
    }

    private function executeEnCoursToTerminee(array &$errors): int
    {
        $count = 0;
        $sorties = $this->simulateEnCoursToTerminee();

        foreach ($sorties as $sortie) {
            try {
                if ($this->sortieStateService->terminerSortie($sortie)) {
                    $count++;
                }
            } catch (\Exception $e) {
                $errors[] = "Erreur fin {$sortie->getNom()}: " . $e->getMessage();
            }
        }

        return $count;
    }

    private function executeTermineeToHistorisee(array &$errors): int
    {
        $count = 0;
        $sorties = $this->simulateTermineeToHistorisee();

        foreach ($sorties as $sortie) {
            try {
                if ($this->sortieStateService->historiserSortie($sortie)) {
                    $count++;
                }
            } catch (\Exception $e) {
                $errors[] = "Erreur archivage {$sortie->getNom()}: " . $e->getMessage();
            }
        }

        return $count;
    }
}
