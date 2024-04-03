<?php

namespace App\Repository;

use App\Entity\Orgaism;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Orgaism>
 *
 * @method Orgaism|null find($id, $lockMode = null, $lockVersion = null)
 * @method Orgaism|null findOneBy(array $criteria, array $orderBy = null)
 * @method Orgaism[]    findAll()
 * @method Orgaism[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrgaismRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orgaism::class);
    }

//    /**
//     * @return Orgaism[] Returns an array of Orgaism objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Orgaism
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
