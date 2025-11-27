<?php

namespace App\Repository;

use App\Entity\WeatherMeasurement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WeatherMeasurement>
 */
class WeatherMeasurementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeatherMeasurement::class);
    }

    public function save(WeatherMeasurement $measurement, bool $flush = true): void
    {
        $em = $this->getEntityManager();

        $em->persist($measurement);

        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Latest measurement for a city (for cache & “current”).
     */
    public function findLatestForCity(string $city): ?WeatherMeasurement
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.city = :city')
            ->setParameter('city', mb_strtolower($city))
            ->orderBy('w.fetchedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Last N measurements for a city (for trend/average).
     *
     * @return WeatherMeasurement[]
     */
    public function findLastNDaysForCity(string $city, int $limit = 10): array
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.city = :city')
            ->setParameter('city', mb_strtolower($city))
            ->orderBy('w.fetchedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
