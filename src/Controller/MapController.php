<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/map')]
class MapController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/lieux', name: 'map_lieux', methods: ['GET'])]
    public function getLieux(): JsonResponse
    {
        $lieux = $this->entityManager->getRepository(Lieu::class)
            ->createQueryBuilder('l')
            ->join('l.ville', 'v')
            ->select('l.id, l.nom, l.rue, l.latitude, l.longitude, v.nom as ville, v.codePostal')
            ->where('l.latitude IS NOT NULL')
            ->andWhere('l.longitude IS NOT NULL')
            ->getQuery()
            ->getResult();

        return new JsonResponse($lieux);
    }

    #[Route('/sorties', name: 'map_sorties', methods: ['GET'])]
    public function getSorties(Request $request): JsonResponse
    {
        $queryBuilder = $this->entityManager->getRepository(Sortie::class)
            ->createQueryBuilder('s')
            ->join('s.lieu', 'l')
            ->join('s.etat', 'e')
            ->join('s.organisateur', 'o')
            ->select('s.id, s.nom, s.dateHeureDebut, s.duree, s.nbInscriptionsMax, s.infosSortie, s.urlPhoto, e.libelle as etat, o.pseudo as organisateur, l.nom as lieu, l.latitude, l.longitude, l.rue, l.ville')
            ->where('l.latitude IS NOT NULL')
            ->andWhere('l.longitude IS NOT NULL');

        // Filtres optionnels
        if ($request->query->get('etat')) {
            $queryBuilder->andWhere('e.libelle = :etat')
                ->setParameter('etat', $request->query->get('etat'));
        }

        if ($request->query->get('site')) {
            $queryBuilder->join('o.site', 'site')
                ->andWhere('site.id = :site')
                ->setParameter('site', $request->query->get('site'));
        }

        if ($request->query->get('date_debut')) {
            $queryBuilder->andWhere('s.dateHeureDebut >= :date_debut')
                ->setParameter('date_debut', new \DateTime($request->query->get('date_debut')));
        }

        if ($request->query->get('date_fin')) {
            $queryBuilder->andWhere('s.dateHeureDebut <= :date_fin')
                ->setParameter('date_fin', new \DateTime($request->query->get('date_fin')));
        }

        $sorties = $queryBuilder->getQuery()->getResult();

        // Ajouter le nombre d'inscrits pour chaque sortie
        foreach ($sorties as &$sortie) {
            $nbInscrits = $this->entityManager->getRepository(Sortie::class)
                ->createQueryBuilder('s')
                ->select('COUNT(i.id)')
                ->join('s.inscriptions', 'i')
                ->where('s.id = :sortieId')
                ->setParameter('sortieId', $sortie['id'])
                ->getQuery()
                ->getSingleScalarResult();

            $sortie['nbInscrits'] = $nbInscrits;
        }

        return new JsonResponse($sorties);
    }

    #[Route('/geocode', name: 'map_geocode', methods: ['POST'])]
    public function geocode(Request $request): JsonResponse
    {
        $address = $request->request->get('address');

        if (!$address) {
            return new JsonResponse(['error' => 'Adresse requise'], 400);
        }

        // Utiliser l'API Nominatim d'OpenStreetMap pour le géocodage
        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'q' => $address,
            'format' => 'json',
            'limit' => 1,
            'countrycodes' => 'fr'
        ]);

        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: SortirApp/1.0\r\n"
            ]
        ]);

        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        if (empty($data)) {
            return new JsonResponse(['error' => 'Adresse non trouvée'], 404);
        }

        $result = $data[0];

        return new JsonResponse([
            'lat' => (float) $result['lat'],
            'lng' => (float) $result['lon'],
            'display_name' => $result['display_name']
        ]);
    }

    #[Route('/reverse-geocode', name: 'map_reverse_geocode', methods: ['POST'])]
    public function reverseGeocode(Request $request): JsonResponse
    {
        $lat = $request->request->get('lat');
        $lng = $request->request->get('lng');

        if (!$lat || !$lng) {
            return new JsonResponse(['error' => 'Coordonnées requises'], 400);
        }

        // Utiliser l'API Nominatim pour le géocodage inverse
        $url = 'https://nominatim.openstreetmap.org/reverse?' . http_build_query([
            'lat' => $lat,
            'lon' => $lng,
            'format' => 'json',
            'addressdetails' => 1
        ]);

        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: SortirApp/1.0\r\n"
            ]
        ]);

        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);

        if (!$data || !isset($data['address'])) {
            return new JsonResponse(['error' => 'Adresse non trouvée'], 404);
        }

        $address = $data['address'];

        return new JsonResponse([
            'display_name' => $data['display_name'],
            'street' => $address['road'] ?? '',
            'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? '',
            'postcode' => $address['postcode'] ?? '',
            'country' => $address['country'] ?? 'France'
        ]);
    }
}
