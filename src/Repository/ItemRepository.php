<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Item>
 *
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function findByValues(array $values, ?Uuid $id = null)
    {
        $query = $this->createQueryBuilder('i');

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $query->andWhere("JSON_EXTRACT(p.values, :key_$key) IN (:val_$key)")
                    ->setParameter("key_$key", "$.$key")
                    ->setParameter("val_$key", $value, ArrayParameterType::STRING);
            } else {
                $query->andWhere("JSON_EXTRACT(p.values, :key_$key) = :val_$key ")
                    ->setParameter("key_$key", "$.$key")
                    ->setParameter("val_$key", $value);
            }
        }

        if ($id) {
            $query->andWhere('p.id != :id')
                ->setParameter('val', $id);
        }

        return $query
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Item[] Returns an array of Item objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Item
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
