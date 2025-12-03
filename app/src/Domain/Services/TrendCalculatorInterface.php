<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Entities\WeatherMeasurement;
use App\Domain\ValueObjects\Temperature;
use App\Domain\ValueObjects\TrendAnalysis;

/**
 * Domain service responsible for analyzing temperature trend
 * based on current temperature and historical measurements.
 */
interface TrendCalculatorInterface
{
    /**
     * @param WeatherMeasurement[] $historicalMeasurements
     *   Historical measurements ordered by time ascending (oldest first).
     */
    public function analyze(
        Temperature $currentTemperature,
        array $historicalMeasurements
    ): TrendAnalysis;
}

