<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Aggregated view of the weather state for a city.
 *
 * Carries the current temperature, optional historical average and the derived trend.
 */
final class WeatherSummary
{
    public function __construct(
        private readonly City $city,
        private readonly Temperature $currentTemperature,
        private readonly ?Temperature $averageTemperature,
        private readonly Trend $trend
    ) {
    }

    public function city(): City
    {
        return $this->city;
    }

    public function currentTemperature(): Temperature
    {
        return $this->currentTemperature;
    }

    public function averageTemperature(): ?Temperature
    {
        return $this->averageTemperature;
    }

    public function trend(): Trend
    {
        return $this->trend;
    }

    public function hasAverage(): bool
    {
        return $this->averageTemperature !== null;
    }

    public function isStable(): bool
    {
        return $this->trend->isStable();
    }
}

