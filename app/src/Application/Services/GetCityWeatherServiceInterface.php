<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\ValueObjects\WeatherSummary;

/**
 * Use case interface for retrieving a weather summary for a city.
 *
 * This interface is what inbound adapters (e.g. controllers) depend on.
 */
interface GetCityWeatherServiceInterface
{
    public function getSummaryForCity(string $cityName): WeatherSummary;
}

