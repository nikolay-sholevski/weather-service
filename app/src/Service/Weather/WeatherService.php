<?php
declare(strict_types=1);

namespace App\Service\Weather;

use App\Repository\WeatherMeasurementRepository;
use Psr\Log\LoggerInterface;

class WeatherResult
{
    public function __construct(
        public readonly string $city,
        public readonly float $temperature,
        public readonly string $trendSign,
        public readonly string $value,
    ) {
    }
}

class WeatherService
{
    public function __construct(
        private readonly WeatherProviderInterface      $weatherProvider,
        private readonly WeatherMeasurementRepository  $measurementRepository,
        private readonly TrendCalculatorInterface      $trendCalculator,
        private readonly LoggerInterface               $logger,
    ) {
    }

    public function getCityWeather(string $city): WeatherResult
    {
        $cityNorm = mb_strtolower($city);
        $this->logger->info('[WeatherService] GetCityWeather called', ['city' => $cityNorm]);

        $currentTemp = $this->weatherProvider->getCurrentTemperature($cityNorm);

        $lastMeasurements = $this->measurementRepository->findLastNDaysForCity($cityNorm, 10);

        $avg       = $this->trendCalculator->calculateAverage($lastMeasurements, $currentTemp);
        $trendSign = $this->trendCalculator->calculateTrendSign($currentTemp, $avg);

        $value = sprintf('%s %s', $currentTemp, $trendSign);

        $this->logger->info('[WeatherService] Weather result prepared', [
            'city'        => $cityNorm,
            'temperature' => $currentTemp,
            'average'     => $avg,
            'trendSign'   => $trendSign,
        ]);

        return new WeatherResult($cityNorm, $currentTemp, $trendSign, $value);
    }
}
