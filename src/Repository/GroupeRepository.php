<?php

namespace App\Repository;

use App\Entity\Groupe;
use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Groupe>
 */
class GroupeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Groupe::class);
    }

    /**
     * Trouve tous les groupes actifs
     */
    public function findActifs(): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les groupes d'un participant
     */
    public function findByParticipant(Participant $participant): array
    {
        return $this->createQueryBuilder('g')
            ->join('g.participants', 'p')
            ->andWhere('p = :participant')
            ->andWhere('g.actif = :actif')
            ->setParameter('participant', $participant)
            ->setParameter('actif', true)
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les groupes créés par un participant
     */
    public function findByCreateur(Participant $createur): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.createur = :createur')
            ->andWhere('g.actif = :actif')
            ->setParameter('createur', $createur)
            ->setParameter('actif', true)
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve un groupe par nom
     */
    public function findByNom(string $nom): ?Groupe
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.nom = :nom')
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Recherche des groupes par nom (recherche partielle)
     */
    public function searchByNom(string $searchTerm): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.nom LIKE :searchTerm')
            ->andWhere('g.actif = :actif')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->setParameter('actif', true)
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les groupes avec le plus de participants
     */
    public function findMostPopular(int $limit = 10): array
    {
        return $this->createQueryBuilder('g')
            ->select('g', 'COUNT(p.id) as participantCount')
            ->leftJoin('g.participants', 'p')
            ->andWhere('g.actif = :actif')
            ->setParameter('actif', true)
            ->groupBy('g.id')
            ->orderBy('participantCount', 'DESC')
            ->addOrderBy('g.nom', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les groupes avec le plus de sorties
     */
    public function findMostActive(int $limit = 10): array
    {
        return $this->createQueryBuilder('g')
            ->select('g', 'COUNT(s.id) as sortieCount')
            ->leftJoin('g.sorties', 's')
            ->andWhere('g.actif = :actif')
            ->setParameter('actif', true)
            ->groupBy('g.id')
            ->orderBy('sortieCount', 'DESC')
            ->addOrderBy('g.nom', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les groupes récemment créés
     */
    public function findRecentlyCreated(int $limit = 10): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.actif = :actif')
            ->setParameter('actif', true)
            ->orderBy('g.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les groupes où un participant peut créer des sorties
     */
    public function findWhereCanCreateSorties(Participant $participant): array
    {
        return $this->createQueryBuilder('g')
            ->join('g.participants', 'p')
            ->andWhere('p = :participant')
            ->andWhere('g.actif = :actif')
            ->setParameter('participant', $participant)
            ->setParameter('actif', true)
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre total de groupes actifs
     */
    public function countActifs(): int
    {
        return $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->andWhere('g.actif = :actif')
            ->setParameter('actif', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les groupes avec des statistiques
     */
    public function findWithStats(): array
    {
        return $this->createQueryBuilder('g')
            ->select('g', 'COUNT(DISTINCT p.id) as participantCount', 'COUNT(DISTINCT s.id) as sortieCount')
            ->leftJoin('g.participants', 'p')
            ->leftJoin('g.sorties', 's')
            ->andWhere('g.actif = :actif')
            ->setParameter('actif', true)
            ->groupBy('g.id')
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les groupes vides (sans participants)
     */
    public function findEmpty(): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.participants', 'p')
            ->andWhere('g.actif = :actif')
            ->andWhere('p.id IS NULL')
            ->setParameter('actif', true)
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les groupes avec un seul participant
     */
    public function findWithOneParticipant(): array
    {
        return $this->createQueryBuilder('g')
            ->select('g', 'COUNT(p.id) as participantCount')
            ->leftJoin('g.participants', 'p')
            ->andWhere('g.actif = :actif')
            ->setParameter('actif', true)
            ->groupBy('g.id')
            ->having('participantCount = 1')
            ->orderBy('g.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
