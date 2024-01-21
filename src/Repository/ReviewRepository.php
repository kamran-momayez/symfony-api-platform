<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 *
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    public function findLatestTopRatedReviewsForCar(int $carId, int $limit = 5): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.car = :carId')
            ->andWhere('r.starRating > 6')
            ->setParameter('carId', $carId)
            ->orderBy('r.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
    }

    public function findOne($order = 'ASC'): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.id', $order)
            ->setMaxResults(1)
            ->getQuery()
            ->getArrayResult();
    }
}
