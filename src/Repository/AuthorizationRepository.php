<?php

namespace App\Repository;

use App\Entity\Authorization;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Authorization>
 *
 * @method Authorization|null find($id, $lockMode = null, $lockVersion = null)
 * @method Authorization|null findOneBy(array $criteria, array $orderBy = null)
 * @method Authorization[]    findAll()
 * @method Authorization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Authorization::class);
    }

    public function findOneByProjectAndToken(string $project_key, string $token): ?User
    {
        return $this->createQueryBuilder('a')
            ->join('a.project', 'p')
            ->andWhere('p.key = :project')
            ->andWhere('a.token = :token')
            ->setParameter('project', $project_key)
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Authorization[] Returns an array of Authorization objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Authorization
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
