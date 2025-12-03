<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\CurrentWeatherProviderInterface;
use App\Application\Ports\WeatherHistoryPortInterface;
use App\Domain\Entities\WeatherMeasurement;
use App\Domain\ValueObjects\City;
use App\Domain\ValueObjects\MeasurementTime;

final class ImportCityWeatherService implements ImportCityWeatherServiceInterface
{
    public function __construct(
        private readonly CurrentWeatherProviderInterface $currentWeatherProvider,
        private readonly WeatherHistoryPortInterface $weatherHistoryPort,
    ) {
    }

    public function importForCity(string $cityName): WeatherMeasurement
    {
        $city = new City($cityName);

        $temperature = $this->currentWeatherProvider->getCurrentTemperature($city);

        $measurementTime = new MeasurementTime(new \DateTimeImmutable('now'));

        $measurement = new WeatherMeasurement(
            null,
            $city,
            $temperature,
            $measurementTime,
        );

        $this->weatherHistoryPort->saveMeasurement($measurement);

        return $measurement;
    }
}
