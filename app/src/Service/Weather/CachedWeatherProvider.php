<?php

namespace App\Service\Weather;

use App\Entity\WeatherMeasurement;
use App\Repository\WeatherMeasurementRepository;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;

class CachedWeatherProvider implements WeatherProviderInterface
{
    public function __construct(
        private readonly WeatherProviderInterface $inner,
        private readonly WeatherMeasurementRepository $repository,
        private readonly LoggerInterface $logger,
        private readonly int $ttlSeconds = 3600, // 1h
    ) {
    }

    public function getCurrentTemperature(string $city): float
    {
        $city = mb_strtolower($city);

        $now = new DateTimeImmutable();

        $latest = $this->repository->findLatestForCity($city);

        if ($latest !== null) {
            $ageSeconds = $now->getTimestamp() - $latest->getFetchedAt()->getTimestamp();

            if ($ageSeconds <= $this->ttlSeconds) {
                $this->logger->info('[CachedWeatherProvider] Cache hit for city', [
                    'city'        => $city,
                    'temperature' => $latest->getTemperature(),
                    'age_seconds' => $ageSeconds,
                ]);

                return $latest->getTemperature();
            }

            $this->logger->info('[CachedWeatherProvider] Cache expired for city', [
                'city'        => $city,
                'temperature' => $latest->getTemperature(),
                'age_seconds' => $ageSeconds,
            ]);
        } else {
            $this->logger->info('[CachedWeatherProvider] No cached measurement found for city', ['city' => $city]);
        }

        $freshTemp = $this->inner->getCurrentTemperature($city);

        $measurement = (new WeatherMeasurement())
            ->setCity($city)
            ->setTemperature($freshTemp)
            ->setFetchedAt($now);

        $this->repository->save($measurement, true);

        $this->logger->info('[CachedWeatherProvider] Stored fresh measurement', [
            'city'        => $city,
            'temperature' => $freshTemp,
        ]);

        return $freshTemp;
    }
}
