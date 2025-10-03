<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\LieuType;
use App\Repository\GroupeRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sorties-map')]
#[IsGranted('ROLE_USER')]
class SortieMapController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SortieRepository $sortieRepository,
        private LieuRepository $lieuRepository,
        private VilleRepository $villeRepository,
        private GroupeRepository $groupeRepository
    ) {
    }

    #[Route('/', name: 'sortie_map_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $view = $request->query->get('view', 'carte'); // 'carte' ou 'liste'

        // Récupérer les villes et groupes pour les filtres
        $villes = $this->villeRepository->findAll();
        $groupes = $this->groupeRepository->findByParticipant($user);

        // Récupérer les filtres de la requête
        $filters = [
            'etat' => $request->query->get('etat'),
            'ville' => $request->query->get('ville'),
            'groupe' => $request->query->get('groupe'),
            'date_debut' => $request->query->get('date_debut'),
            'date_fin' => $request->query->get('date_fin')
        ];

        // Récupérer les sorties selon les filtres
        $sorties = $this->getFilteredSorties($filters, $user);

        return $this->render('sortie_map/index.html.twig', [
            'sorties' => $sorties,
            'villes' => $villes,
            'groupes' => $groupes,
            'filters' => $filters,
            'view' => $view,
            'user' => $user
        ]);
    }

    #[Route('/api/sorties', name: 'sortie_map_api_sorties', methods: ['GET'])]
    public function getSortiesApi(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $filters = [
            'etat' => $request->query->get('etat'),
            'ville' => $request->query->get('ville'),
            'groupe' => $request->query->get('groupe'),
            'date_debut' => $request->query->get('date_debut'),
            'date_fin' => $request->query->get('date_fin')
        ];

        $sorties = $this->getFilteredSorties($filters, $user);

        // Formater les données pour la carte
        $sortiesData = [];
        foreach ($sorties as $sortie) {
            if ($sortie->getLieu()->getLatitude() && $sortie->getLieu()->getLongitude()) {
                $sortiesData[] = [
                    'id' => $sortie->getId(),
                    'nom' => $sortie->getNom(),
                    'dateHeureDebut' => $sortie->getDateHeureDebut()->format('Y-m-d H:i:s'),
                    'duree' => $sortie->getDuree(),
                    'nbInscriptionsMax' => $sortie->getNbInscriptionsMax(),
                    'nbInscriptionsActuelles' => $sortie->getNbInscriptionsActuelles(),
                    'infosSortie' => $sortie->getInfosSortie(),
                    'urlPhoto' => $sortie->getUrlPhoto(),
                    'etat' => $sortie->getEtat()->getLibelle(),
                    'organisateur' => $sortie->getOrganisateur()->getPseudo(),
                    'lieu' => $sortie->getLieu()->getNom(),
                    'rue' => $sortie->getLieu()->getRue(),
                    'latitude' => $sortie->getLieu()->getLatitude(),
                    'longitude' => $sortie->getLieu()->getLongitude(),
                    'ville' => $sortie->getLieu()->getVille()->getNom(),
                    'codePostal' => $sortie->getLieu()->getVille()->getCodePostal(),
                    'groupe' => $sortie->getGroupe() ? [
                        'id' => $sortie->getGroupe()->getId(),
                        'nom' => $sortie->getGroupe()->getNom()
                    ] : null,
                    'isPrivee' => $sortie->isPrivee(),
                    'canVoir' => $sortie->canVoir($user),
                    'canSInscrire' => $sortie->canSInscrire($user)
                ];
            }
        }

        return new JsonResponse($sortiesData);
    }

    #[Route('/api/lieux', name: 'sortie_map_api_lieux', methods: ['GET'])]
    public function getLieuxApi(): JsonResponse
    {
        $lieux = $this->lieuRepository->createQueryBuilder('l')
            ->join('l.ville', 'v')
            ->select('l.id, l.nom, l.rue, l.latitude, l.longitude, v.nom as ville, v.codePostal')
            ->where('l.latitude IS NOT NULL')
            ->andWhere('l.longitude IS NOT NULL')
            ->getQuery()
            ->getResult();

        return new JsonResponse($lieux);
    }

    #[Route('/lieu/nouveau', name: 'sortie_map_lieu_new', methods: ['GET', 'POST'])]
    public function newLieu(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            // Requête AJAX - traiter les données brutes
            $nom = $request->request->get('nom');
            $rue = $request->request->get('rue');
            $latitude = $request->request->get('latitude');
            $longitude = $request->request->get('longitude');
            $villeId = $request->request->get('ville');

            // Validation basique
            if (!$nom || !$latitude || !$longitude || !$villeId) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => ['Tous les champs obligatoires doivent être remplis']
                ]);
            }

            try {
                $lieu = new Lieu();
                $lieu->setNom($nom);
                $lieu->setRue($rue);
                $lieu->setLatitude((float)$latitude);
                $lieu->setLongitude((float)$longitude);

                $ville = $this->entityManager->getRepository(Ville::class)->find($villeId);
                if (!$ville) {
                    return new JsonResponse([
                        'success' => false,
                        'errors' => ['Ville non trouvée']
                    ]);
                }
                $lieu->setVille($ville);

                $this->entityManager->persist($lieu);
                $this->entityManager->flush();

                return new JsonResponse([
                    'success' => true,
                    'lieu' => [
                        'id' => $lieu->getId(),
                        'nom' => $lieu->getNom(),
                        'rue' => $lieu->getRue(),
                        'latitude' => $lieu->getLatitude(),
                        'longitude' => $lieu->getLongitude(),
                        'ville' => $lieu->getVille()->getNom(),
                        'codePostal' => $lieu->getVille()->getCodePostal()
                    ]
                ]);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => ['Erreur lors de la création: ' . $e->getMessage()]
                ]);
            }
        }

        // Requête GET normale - afficher le formulaire
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);

        return $this->render('sortie_map/_lieu_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/lieu/{id}', name: 'sortie_map_lieu_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showLieu(Lieu $lieu): JsonResponse
    {
        return new JsonResponse([
            'id' => $lieu->getId(),
            'nom' => $lieu->getNom(),
            'rue' => $lieu->getRue(),
            'latitude' => $lieu->getLatitude(),
            'longitude' => $lieu->getLongitude(),
            'ville' => $lieu->getVille()->getNom(),
            'codePostal' => $lieu->getVille()->getCodePostal(),
            'sorties' => array_map(function($sortie) {
                return [
                    'id' => $sortie->getId(),
                    'nom' => $sortie->getNom(),
                    'dateHeureDebut' => $sortie->getDateHeureDebut()->format('Y-m-d H:i:s'),
                    'etat' => $sortie->getEtat()->getLibelle()
                ];
            }, $lieu->getSorties()->toArray())
        ]);
    }

    private function getFilteredSorties(array $filters, $user): array
    {
        $queryBuilder = $this->sortieRepository->createQueryBuilder('s')
            ->join('s.lieu', 'l')
            ->join('s.etat', 'e')
            ->join('s.organisateur', 'o')
            ->leftJoin('s.groupe', 'g')
            ->where('l.latitude IS NOT NULL')
            ->andWhere('l.longitude IS NOT NULL');

        // Filtre par état
        if (!empty($filters['etat'])) {
            $queryBuilder->andWhere('e.libelle = :etat')
                ->setParameter('etat', $filters['etat']);
        }

        // Filtre par ville
        if (!empty($filters['ville'])) {
            $queryBuilder->andWhere('l.ville = :ville')
                ->setParameter('ville', $filters['ville']);
        }

        // Filtre par groupe
        if (!empty($filters['groupe'])) {
            $queryBuilder->andWhere('g.id = :groupe')
                ->setParameter('groupe', $filters['groupe']);
        }

        // Filtre par date de début
        if (!empty($filters['date_debut'])) {
            $queryBuilder->andWhere('s.dateHeureDebut >= :date_debut')
                ->setParameter('date_debut', new \DateTime($filters['date_debut']));
        }

        // Filtre par date de fin
        if (!empty($filters['date_fin'])) {
            $queryBuilder->andWhere('s.dateHeureDebut <= :date_fin')
                ->setParameter('date_fin', new \DateTime($filters['date_fin']));
        }

        $sorties = $queryBuilder->getQuery()->getResult();

        // Filtrer les sorties selon les permissions de groupe
        $sortiesVisibles = [];
        foreach ($sorties as $sortie) {
            if ($sortie->canVoir($user)) {
                $sortiesVisibles[] = $sortie;
            }
        }

        return $sortiesVisibles;
    }
}
