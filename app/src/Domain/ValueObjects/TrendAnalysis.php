<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Represents the result of analyzing temperature history relative to a current temperature.
 *
 * Contains:
 * - The derived trend (hotter / colder / stable).
 * - The historical average temperature (if available).
 */
final class TrendAnalysis
{
    public function __construct(
        private readonly Trend $trend,
        private readonly ?Temperature $averageTemperature,
    ) {
    }

    public function trend(): Trend
    {
        return $this->trend;
    }

    public function averageTemperature(): ?Temperature
    {
        return $this->averageTemperature;
    }

    public function hasAverage(): bool
    {
        return $this->averageTemperature !== null;
    }
}
