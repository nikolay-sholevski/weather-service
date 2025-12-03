<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\CurrentWeatherProviderInterface;
use App\Application\Ports\WeatherHistoryPortInterface;
use App\Domain\Services\TrendCalculatorInterface;
use App\Domain\ValueObjects\City;
use App\Domain\ValueObjects\WeatherSummary;

/**
 * Default implementation of the GetCityWeather use case.
 *
 * Responsibilities:
 * - Normalize the input city name into a City value object.
 * - Obtain current temperature via outbound HTTP port.
 * - Obtain historical measurements via outbound history port.
 * - Delegate analysis (average + trend) entirely to a domain service.
 * - Build a WeatherSummary from the domain analysis result.
 */
final class GetCityWeatherService implements GetCityWeatherServiceInterface
{
    public function __construct(
        private readonly CurrentWeatherProviderInterface $currentWeatherProvider,
        private readonly WeatherHistoryPortInterface $weatherHistoryPort,
        private readonly TrendCalculatorInterface $trendCalculator,
    ) {
    }

    public function getSummaryForCity(string $cityName): WeatherSummary
    {
        $city = new City($cityName);

        $currentTemperature = $this->currentWeatherProvider
            ->getCurrentTemperature($city);

        $historicalMeasurements = $this->weatherHistoryPort
            ->findMeasurementsForLastNDays($city, 10);

        $analysis = $this->trendCalculator
            ->analyze($currentTemperature, $historicalMeasurements);

        return new WeatherSummary(
            $city,
            $currentTemperature,
            $analysis->averageTemperature(),
            $analysis->trend(),
        );
    }
}
