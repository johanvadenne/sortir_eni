<?php

namespace App\Repository;

use App\Entity\Sortie;
use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function save(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sortie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve les sorties avec des filtres avancés
     */
    public function findWithFilters(
        array $criteria = [],
        ?string $periode = null,
        ?string $recherche = null,
        ?bool $organisateur = null,
        ?bool $inscrit = null,
        ?bool $nonInscrit = null,
        ?bool $passees = null,
        ?Participant $user = null
    ): array {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.etat', 'e')
            ->leftJoin('s.lieu', 'l')
            ->leftJoin('l.ville', 'v')
            ->leftJoin('s.organisateur', 'o')
            ->leftJoin('o.site', 'site')
            ->leftJoin('s.inscriptions', 'i')
            ->addSelect('e', 'l', 'v', 'o', 'site', 'i')
            ->orderBy('s.dateHeureDebut', 'ASC');

        // Filtre par site
        if (isset($criteria['site']) && $criteria['site']) {
            $qb->andWhere('site.id = :siteId')
               ->setParameter('siteId', $criteria['site']);
        }

        // Filtre par état
        if (isset($criteria['etat']) && $criteria['etat']) {
            $qb->andWhere('e.libelle = :etat')
               ->setParameter('etat', $criteria['etat']);
        }

        // Filtre par période
        if ($periode) {
            $now = new \DateTime();
            switch ($periode) {
                case 'aujourdhui':
                    $qb->andWhere('DATE(s.dateHeureDebut) = DATE(:now)')
                       ->setParameter('now', $now);
                    break;
                case 'semaine':
                    $qb->andWhere('s.dateHeureDebut BETWEEN :now AND :week')
                       ->setParameter('now', $now)
                       ->setParameter('week', (clone $now)->modify('+1 week'));
                    break;
                case 'mois':
                    $qb->andWhere('s.dateHeureDebut BETWEEN :now AND :month')
                       ->setParameter('now', $now)
                       ->setParameter('month', (clone $now)->modify('+1 month'));
                    break;
            }
        }

        // Filtre par recherche (nom de la sortie)
        if ($recherche) {
            $qb->andWhere('s.nom LIKE :recherche')
               ->setParameter('recherche', '%' . $recherche . '%');
        }

        // Filtre par organisateur (mes sorties)
        if ($organisateur && $user) {
            $qb->andWhere('o.id = :userId')
               ->setParameter('userId', $user->getId());
        }

        // Filtre par inscription (je suis inscrit)
        if ($inscrit && $user) {
            $qb->andWhere('i.participant = :user')
               ->setParameter('user', $user);
        }

        // Filtre par non-inscription (je ne suis pas inscrit)
        if ($nonInscrit && $user) {
            $qb->andWhere('s.id NOT IN (
                SELECT s2.id FROM App\Entity\Sortie s2
                JOIN s2.inscriptions i2
                WHERE i2.participant = :user
            )')
               ->setParameter('user', $user);
        }

        // Filtre par sorties passées
        if ($passees) {
            $qb->andWhere('s.dateHeureDebut < :now')
               ->setParameter('now', new \DateTime());
        }

        return $qb->getQuery()->getResult();
    }
}
