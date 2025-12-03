<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Domain\ValueObjects\WeatherSummary as DomainWeatherSummary;

/**
 * Simple DTO representation of a WeatherSummary for serialization / API responses.
 */
final class WeatherSummaryDto
{
    public function __construct(
        public string $city,
        public float $current,
        public ?float $average,
        public string $trendDirection,
        public float $trendDelta,
        public string $trendLabel,
    ) {
    }

    public static function fromDomain(DomainWeatherSummary $summary): self
    {
        $city = (string) $summary->city();
        $current = $summary->currentTemperature()->value();
        $average = $summary->hasAverage()
            ? $summary->averageTemperature()?->value()
            : null;

        $trend = $summary->trend();

        return new self(
            $city,
            $current,
            $average,
            $trend->direction(),
            $trend->deltaInCelsius(),
            $trend->label(),
        );
    }

    /**
     * To array for JSON responses etc.
     *
     * @return array{
     *   city:string,
     *   current:float,
     *   average:float|null,
     *   trend:array{direction:string,delta:float,label:string}
     * }
     */
    public function toArray(): array
    {
        return [
            'city'    => $this->city,
            'current' => $this->current,
            'average' => $this->average,
            'trend'   => [
                'direction' => $this->trendDirection,
                'delta'     => $this->trendDelta,
                'label'     => $this->trendLabel,
            ],
        ];
    }
}

