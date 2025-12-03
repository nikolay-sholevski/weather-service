<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entities\WeatherMeasurement;

interface ImportCityWeatherServiceInterface
{
    /**
     * Imports current weather for a single city and persists it.
     */
    public function importForCity(string $cityName): WeatherMeasurement;
}
