<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Entities\WeatherMeasurement;
use App\Domain\ValueObjects\Temperature;
use App\Domain\ValueObjects\Trend;

/**
 * Simple trend calculator that:
 * - Computes the arithmetic average of the historical temperatures.
 * - Compares the current temperature to that average.
 * - Classifies the trend as hotter / colder / stable based on a threshold.
 *
 * This intentionally avoids complex statistical formulas as per the assignment.
 */
final class SimpleTrendCalculator implements TrendCalculatorInterface
{
    public function __construct(
        private readonly float $stableThresholdCelsius = 0.3
    ) {
        if ($stableThresholdCelsius < 0.0) {
            throw new \InvalidArgumentException('Stable threshold cannot be negative.');
        }
    }

    public function calculateTrend(
        Temperature $currentTemperature,
        array $historicalMeasurements
    ): Trend {
        if ($historicalMeasurements === []) {
            return new Trend(Trend::DIRECTION_STABLE, 0.0);
        }

        $sum = 0.0;
        $count = 0;

        foreach ($historicalMeasurements as $measurement) {
            if (!$measurement instanceof WeatherMeasurement) {
                throw new \InvalidArgumentException('Expected array of WeatherMeasurement instances.');
            }

            $sum += $measurement->temperature()->value();
            $count++;
        }

        if ($count === 0) {
            return new Trend(Trend::DIRECTION_STABLE, 0.0);
        }

        $averageValue = $sum / $count;
        $average = new Temperature($averageValue);

        $delta = $currentTemperature->difference($average);
        $absDelta = \abs($delta);

        if ($absDelta < $this->stableThresholdCelsius) {
            return new Trend(Trend::DIRECTION_STABLE, $delta);
        }

        $direction = $delta > 0
            ? Trend::DIRECTION_HOTTER
            : Trend::DIRECTION_COLDER;

        return new Trend($direction, $delta);
    }
}

