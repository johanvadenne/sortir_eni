<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'home')]
    public function index(Request $request, SortieRepository $sortieRepository): Response
    {
        // Récupérer les paramètres de filtrage
        $siteId = $request->query->get('site');
        $etat = $request->query->get('etat');
        $periode = $request->query->get('periode');
        $recherche = $request->query->get('recherche');
        $organisateur = $request->query->get('organisateur');
        $inscrit = $request->query->get('inscrit');
        $nonInscrit = $request->query->get('non_inscrit');
        $passees = $request->query->get('passees');

        // Construire les critères de filtrage
        $criteria = [];

        if ($siteId) {
            $criteria['site'] = $siteId;
        }

        if ($etat) {
            $criteria['etat'] = $etat;
        }

        // Récupérer les sorties avec filtres
        $sorties = $sortieRepository->findWithFilters(
            $criteria,
            $periode,
            $recherche,
            $organisateur,
            $inscrit,
            $nonInscrit,
            $passees,
            $this->getUser()
        );

        // Calculer si l'utilisateur est inscrit à chaque sortie
        $user = $this->getUser();
        $sortiesWithInscription = [];

        foreach ($sorties as $sortie) {
            $isInscrit = false;
            if ($user) {
                // Vérifier si l'utilisateur est inscrit à cette sortie
                foreach ($sortie->getInscriptions() as $inscription) {
                    if ($inscription->getParticipant() === $user) {
                        $isInscrit = true;
                        break;
                    }
                }
            }

            $sortiesWithInscription[] = [
                'sortie' => $sortie,
                'is_inscrit' => $isInscrit
            ];
        }

        // Récupérer tous les sites pour le filtre
        $sites = $this->entityManager->getRepository(Site::class)->findAll();

        // Récupérer tous les états pour le filtre
        $etats = $this->entityManager->getRepository(\App\Entity\Etat::class)->findAll();

        return $this->render('home/index.html.twig', [
            'sorties' => $sortiesWithInscription,
            'sites' => $sites,
            'etats' => $etats,
            'filters' => [
                'site' => $siteId,
                'etat' => $etat,
                'periode' => $periode,
                'recherche' => $recherche,
                'organisateur' => $organisateur,
                'inscrit' => $inscrit,
                'non_inscrit' => $nonInscrit,
                'passees' => $passees,
            ]
        ]);
    }
}