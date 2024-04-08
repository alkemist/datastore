<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\Store;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
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

    /**
     * Recherche avec filtres
     * @param User $user
     * @param Store $store
     * @param array $filters
     * @return float|int|mixed|string
     */
    public function findByFilters(User $user, Store $store, array $filters): mixed
    {
        $query = $this->createQueryBuilder('i');

        foreach ($filters as $field_key => $field_value) {
            if ($field_key === 'author') {
                $query->andWhere(
                    $query->expr()->like("i.author", ":author")
                )
                    ->setParameter("author", $field_value);
            } else if ($field_key === 'slug') {
                $query->andWhere(
                    $query->expr()->like("i.slug", ":slug")
                )
                    ->setParameter("slug", $field_value);
            } else {
                if (is_array($field_value)) {
                    $orX = $query->expr()->orX();
                    foreach ($field_value as $index => $value) {
                        $orX->add(
                            $query->expr()->like(
                                "JSON_GET_TEXT(i.values, :key_$field_key$index)", ":val_$field_key$index"
                            )
                        );
                        $query->setParameter("key_$field_key$index", $field_key)
                            ->setParameter("val_$field_key$index", "%" . $value . "%");
                    }
                    $query->andWhere($orX);
                } else {
                    $query->andWhere(
                        $query->expr()->like("JSON_GET_TEXT(i.values, :key_$field_key)", ":val_$field_key")
                    )
                        ->setParameter("key_$field_key", $field_key)
                        ->setParameter("val_$field_key", "%" . $field_value . "%");
                }
            }
        }

        $query->andWhere('i.store = :store')
            ->setParameter('store', $store);

        return $query
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche d'existence
     * @param User $user
     * @param Store $store
     * @param array $values
     * @param Uuid|null $id
     * @return float|int|mixed|string
     */
    public function findExistingItems(User $user, Store $store, array $values, ?Uuid $id = null): mixed
    {
        $query = $this->createQueryBuilder('i');

        foreach ($values as $field_key => $field_value) {
            if ($field_key === 'slug') {
                $query->orWhere(
                    $query->expr()->like("i.slug", ":slug")
                )
                    ->setParameter("slug", $field_value);
            } else {
                if (is_array($field_value)) {
                    $orX = $query->expr()->orX();
                    foreach ($field_value as $index => $value) {
                        $orX->add(
                            $query->expr()->like(
                                "JSON_GET_TEXT(i.values, :key_$field_key$index)", ":val_$field_key$index"
                            )
                        );
                        $query->setParameter("key_$field_key$index", $field_key)
                            ->setParameter("val_$field_key$index", "%" . $value . "%");
                    }
                    $query->orWhere($orX);
                } else {
                    $query->orWhere(
                        $query->expr()->like("JSON_GET_TEXT(i.values, :key_$field_key)", ":val_$field_key")
                    )
                        ->setParameter("key_$field_key", $field_key)
                        ->setParameter("val_$field_key", "%" . $field_value . "%");
                }
            }
        }

        if ($id) {
            $query->andWhere('i.id != :id')
                ->setParameter('id', $id);
        }

        $query->andWhere('i.store = :store')
            ->setParameter('store', $store);

        return $query
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneByUserAndSlug(User $user, Store $store, $slug): ?Item
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.store = :store')
            ->andWhere('i.slug = :slug')
            ->setParameter('store', $store)
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneBySlug(Store $store, $slug): ?Item
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.store = :store')
            ->andWhere('i.slug = :slug')
            ->setParameter('store', $store)
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByStore(User $user, Store $store): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.store = :store')
            ->setParameter('store', $store)
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
