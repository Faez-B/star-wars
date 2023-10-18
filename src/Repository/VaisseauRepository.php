<?php

namespace App\Repository;

use App\Entity\Vaisseau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vaisseau>
 *
 * @method Vaisseau|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vaisseau|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vaisseau[]    findAll()
 * @method Vaisseau[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VaisseauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vaisseau::class);
    }

//    /**
//     * @return Vaisseau[] Returns an array of Vaisseau objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Vaisseau
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
