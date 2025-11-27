<?php

namespace App\Service\Weather;

use App\Entity\WeatherMeasurement;
use App\Repository\WeatherMeasurementRepository;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class HourlyWeatherFetcher
{
    /**
     * @param WeatherProviderInterface $weatherProvider
     */
    public function __construct(
        private readonly WeatherProviderInterface $weatherProvider,
        private readonly WeatherMeasurementRepository $measurementRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function refreshCity(string $city): void
    {
        $cityNorm = mb_strtolower($city);

        $this->logger->info('[HourlyWeatherFetcher] Refreshing city', [
            'city' => $cityNorm,
        ]);

        $temp = $this->weatherProvider->getCurrentTemperature($cityNorm);

        $measurement = (new WeatherMeasurement())
            ->setCity($cityNorm)
            ->setTemperature($temp)
            ->setFetchedAt(new DateTimeImmutable());

        $this->measurementRepository->save($measurement);

        $this->logger->info('[HourlyWeatherFetcher] Stored measurement', [
            'city'        => $cityNorm,
            'temperature' => $temp,
        ]);
    }

    /**
     * @param string[] $cities
     */
    public function refreshMany(array $cities): void
    {
        foreach ($cities as $city) {
            $this->refreshCity($city);
        }
    }
}
