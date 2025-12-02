<?php
declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\Entities\WeatherMeasurement;
use App\Domain\ValueObjects\Trend;

final class WeatherSummary
{
    public function __construct(
        public readonly string $city,
        public readonly float $temperature,
        public readonly string $trendSign,   // "up" | "down" | "stable"
        public readonly float $trendValue,   // magnitude, e.g. 1.5°C
        public readonly \DateTimeImmutable $measuredAt,
    ) {
    }

    /**
     * Factory that translates domain objects into a DTO
     * suitable for controllers / views / API responses.
     */
    public static function fromDomain(
        WeatherMeasurement $measurement,
        Trend $trend
    ): self {
        return new self(
            city: (string) $measurement->city(),
            temperature: $measurement->temperature()->value,
            trendSign: $trend->sign,
            trendValue: $trend->magnitude,
            measuredAt: $measurement->measuredAt(),
        );
    }
}

