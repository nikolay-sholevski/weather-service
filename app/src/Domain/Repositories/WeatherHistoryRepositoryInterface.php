<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\WeatherMeasurement;
use App\Domain\ValueObjects\City;

/**
 * Outbound port for obtaining historical weather measurements.
 *
 * Implementations may fetch data from a database, external API, cache, etc.
 */
interface WeatherHistoryRepositoryInterface
{
    /**
     * Returns measurements for the given city within the last $days days.
     *
     * The returned array SHOULD be ordered by measurement time ascending (oldest first),
     * so that domain services relying on chronological order can safely use it.
     *
     * @return WeatherMeasurement[] List of measurements ordered by time ascending.
     */
    public function findMeasurementsForLastNDays(City $city, int $days): array;
}

