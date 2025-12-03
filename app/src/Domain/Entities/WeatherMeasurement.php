<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\City;
use App\Domain\ValueObjects\MeasurementTime;
use App\Domain\ValueObjects\Temperature;

/**
 * Represents a single weather measurement taken for a city at a specific time.
 *
 * Invariants:
 * - A measurement always has a city, temperature and measurement time.
 * - These properties are immutable from a domain perspective.
 */
final class WeatherMeasurement
{
    /**
     * @param int|null $id Optional persistence identity (not strictly required for domain logic).
     */
    public function __construct(
        private readonly ?int $id,
        private readonly City $city,
        private readonly Temperature $temperature,
        private readonly MeasurementTime $measurementTime,
    ) {
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function city(): City
    {
        return $this->city;
    }

    public function temperature(): Temperature
    {
        return $this->temperature;
    }

    public function measurementTime(): MeasurementTime
    {
        return $this->measurementTime;
    }

    public function isForCity(City $city): bool
    {
        return $this->city->equals($city);
    }

    public function isBefore(self $other): bool
    {
        return $this->measurementTime->isBefore($other->measurementTime);
    }

    public function isAfter(self $other): bool
    {
        return $this->measurementTime->isAfter($other->measurementTime);
    }
}
