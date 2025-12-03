<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Ports\WeatherHistoryPortInterface;
use App\Domain\Entities\WeatherMeasurement;
use App\Domain\ValueObjects\City;
use App\Domain\ValueObjects\MeasurementTime;
use App\Domain\ValueObjects\Temperature;
use DateInterval;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;

/**
 * Doctrine DBAL-based implementation of WeatherHistoryPortInterface.
 *
 * Uses raw SQL/QueryBuilder to interact with the "weather_measurements" table
 * and hydrates domain WeatherMeasurement objects.
 */
final class WeatherHistoryDoctrineAdapter implements WeatherHistoryPortInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @return WeatherMeasurement[]
     */
    public function findMeasurementsForLastNDays(City $city, int $days): array
    {
        $now = new DateTimeImmutable('now');
        $from = $now->sub(new DateInterval(\sprintf('P%dD', $days)));

        $sql = <<<'SQL'
SELECT id, city_name, temperature_celsius, measured_at
FROM weather_measurements
WHERE LOWER(city_name) = :city
  AND measured_at >= :from
ORDER BY measured_at ASC
SQL;

        $rows = $this->connection->fetchAllAssociative($sql, [
            'city' => \mb_strtolower($city->value()),
            'from' => $from->format('Y-m-d H:i:s'),
        ]);

        $measurements = [];

        foreach ($rows as $row) {
            $measurements[] = new WeatherMeasurement(
                isset($row['id']) ? (int) $row['id'] : null,
                new City($row['city_name']),
                new Temperature((float) $row['temperature_celsius']),
                MeasurementTime::fromString($row['measured_at']),
            );
        }

        return $measurements;
    }

    public function saveMeasurement(WeatherMeasurement $measurement): void
    {
        $city = $measurement->city();                    // City VO
        $temperature = $measurement->temperature();      // Temperature VO
        $time = $measurement->measurementTime();        // MeasurementTime VO

        $this->connection->insert('weather_measurements', [
            'city_name'           => \mb_strtolower($city->value()),
            'temperature_celsius' => $temperature->value(),              // or ->toFloat()
            'measured_at'         => $time->value()->format('Y-m-d H:i:s'),
        ]);
    }
}
