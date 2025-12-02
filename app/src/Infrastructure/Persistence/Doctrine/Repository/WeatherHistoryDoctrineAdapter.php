<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Ports\WeatherHistoryPortInterface;
use App\Domain\Entities\WeatherMeasurement;
use App\Domain\ValueObjects\City;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Doctrine-based implementation of WeatherHistoryPortInterface.
 *
 * This is an outbound adapter: it satisfies the application's need
 * for historical measurements using a relational database.
 */
final class WeatherHistoryDoctrineAdapter implements WeatherHistoryPortInterface
{
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        /** @var EntityRepository $repo */
        $repo = $entityManager->getRepository(WeatherMeasurement::class);
        $this->repository = $repo;
    }

    public function findMeasurementsForLastNDays(City $city, int $days): array
    {
        $now = new DateTimeImmutable('now');
        $from = $now->sub(new DateInterval(\sprintf('P%dD', $days)));

        // Assuming WeatherMeasurement is a Doctrine entity,
        // with fields: cityName (string), measurementTime (DateTimeImmutable)
        // and temperature (float) mapped appropriately.
        return $this->repository->createQueryBuilder('m')
            ->andWhere('LOWER(m.cityName) = :city')
            ->andWhere('m.measurementTime >= :from')
            ->setParameter('city', \mb_strtolower($city->value()))
            ->setParameter('from', $from)
            ->orderBy('m.measurementTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

