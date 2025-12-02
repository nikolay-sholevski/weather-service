<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Entities\WeatherMeasurement;
use App\Domain\ValueObjects\Temperature;
use App\Domain\ValueObjects\Trend;
use App\Domain\ValueObjects\TrendAnalysis;

/**
 * Simple trend calculator that:
 * - Computes the arithmetic average of the historical temperatures (if available).
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

    public function analyze(
        Temperature $currentTemperature,
        array $historicalMeasurements
    ): TrendAnalysis {
        if ($historicalMeasurements === []) {
            $trend = new Trend(Trend::DIRECTION_STABLE, 0.0);

            return new TrendAnalysis($trend, null);
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
            $trend = new Trend(Trend::DIRECTION_STABLE, 0.0);

            return new TrendAnalysis($trend, null);
        }

        $average = new Temperature($sum / $count);

        $delta = $currentTemperature->difference($average);
        $absDelta = \abs($delta);

        if ($absDelta < $this->stableThresholdCelsius) {
            $trend = new Trend(Trend::DIRECTION_STABLE, $delta);

            return new TrendAnalysis($trend, $average);
        }

        $direction = $delta > 0
            ? Trend::DIRECTION_HOTTER
            : Trend::DIRECTION_COLDER;

        $trend = new Trend($direction, $delta);

        return new TrendAnalysis($trend, $average);
    }
}

