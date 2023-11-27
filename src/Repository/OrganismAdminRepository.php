<?php

namespace App\Repository;

use App\Entity\OrganismAdmin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganismAdmin>
 *
 * @method OrganismAdmin|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrganismAdmin|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrganismAdmin[]    findAll()
 * @method OrganismAdmin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganismAdminRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganismAdmin::class);
    }

//    /**
//     * @return OrganismAdmin[] Returns an array of OrganismAdmin objects
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

//    public function findOneBySomeField($value): ?OrganismAdmin
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
