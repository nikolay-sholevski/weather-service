<?php

declare(strict_types=1);

namespace App\Application\Ports;

use App\Domain\Entities\WeatherMeasurement;
use App\Domain\ValueObjects\City;

/**
 * Outbound port for accessing historical weather data.
 *
 * Application layer uses this to obtain measurements from
 * any underlying storage (database, external API, etc.).
 */
interface WeatherHistoryPortInterface
{
    /**
     * Returns measurements for the given city within the last $days days.
     *
     * The returned array SHOULD be ordered by measurement time ascending (oldest first).
     *
     * @return WeatherMeasurement[] List of measurements ordered by time ascending.
     */
    public function findMeasurementsForLastNDays(City $city, int $days): array;

    public function saveMeasurement(WeatherMeasurement $measurement): void;
}
