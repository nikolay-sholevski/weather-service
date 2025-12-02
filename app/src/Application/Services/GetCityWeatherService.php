<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\CurrentWeatherProviderInterface;
use App\Application\Ports\WeatherHistoryPortInterface;
use App\Domain\Entities\WeatherMeasurement;
use App\Domain\Services\TrendCalculatorInterface;
use App\Domain\ValueObjects\City;
use App\Domain\ValueObjects\Temperature;
use App\Domain\ValueObjects\WeatherSummary;

/**
 * Default implementation of the GetCityWeather use case.
 *
 * Responsibilities:
 * - Normalize the input city name into a City value object.
 * - Obtain current temperature via outbound HTTP port.
 * - Obtain historical measurements via outbound history port.
 * - Delegate trend calculation to a domain service.
 * - Optionally compute the historical average for inclusion in the summary.
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

        $trend = $this->trendCalculator
            ->calculateTrend($currentTemperature, $historicalMeasurements);

        $average = $this->computeAverageOrNull($historicalMeasurements);

        return new WeatherSummary(
            $city,
            $currentTemperature,
            $average,
            $trend
        );
    }

    /**
     * @param WeatherMeasurement[] $measurements
     */
    private function computeAverageOrNull(array $measurements): ?Temperature
    {
        if ($measurements === []) {
            return null;
        }

        $sum = 0.0;
        $count = 0;

        foreach ($measurements as $measurement) {
            if (!$measurement instanceof WeatherMeasurement) {
                throw new \InvalidArgumentException('Expected array of WeatherMeasurement instances.');
            }

            $sum += $measurement->temperature()->value();
            $count++;
        }

        if ($count === 0) {
            return null;
        }

        return new Temperature($sum / $count);
    }
}

