<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Entities\WeatherMeasurement;
use App\Domain\ValueObjects\Temperature;
use App\Domain\ValueObjects\Trend;

/**
 * Domain service responsible for calculating the temperature trend
 * based on current temperature and historical measurements.
 */
interface TrendCalculatorInterface
{
    /**
     * @param WeatherMeasurement[] $historicalMeasurements
     *   Historical measurements ordered by time ascending (oldest first).
     */
    public function calculateTrend(
        Temperature $currentTemperature,
        array $historicalMeasurements
    ): Trend;
}

