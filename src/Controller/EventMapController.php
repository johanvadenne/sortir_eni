<?php

namespace App\Controller;

use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/events-map')]
class EventMapController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'events_map')]
    public function index(Request $request): Response
    {
        // Récupérer les sites pour les filtres
        $sites = $this->entityManager->getRepository(Site::class)->findAll();

        // Récupérer les filtres de la requête
        $filters = [
            'etat' => $request->query->get('etat'),
            'site' => $request->query->get('site'),
            'date_debut' => $request->query->get('date_debut'),
            'date_fin' => $request->query->get('date_fin')
        ];

        return $this->render('events_map/index.html.twig', [
            'sites' => $sites,
            'filters' => $filters
        ]);
    }
}
