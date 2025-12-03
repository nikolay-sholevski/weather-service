<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use App\Application\Services\GetCityWeatherServiceInterface;
use App\Domain\ValueObjects\City;
use App\Domain\ValueObjects\Temperature;
use App\Domain\ValueObjects\Trend;
use App\Domain\ValueObjects\WeatherSummary;
use Redis;

/**
 * Cached decorator for GetCityWeatherServiceInterface.
 *
 * Caches the final WeatherSummary per city to avoid:
 * - External API calls
 * - DB queries
 * - Trend calculation on cache hit
 */
final class CachedGetCityWeatherService implements GetCityWeatherServiceInterface
{
    public function __construct(
        private readonly GetCityWeatherServiceInterface $inner,
        private readonly Redis $redis,
        private readonly int $ttlSeconds = 300, // e.g. 5 minutes
    ) {
    }

    public function getSummaryForCity(string $cityName): WeatherSummary
    {
        $normalizedName = \mb_strtolower(\trim($cityName));
        $key = \sprintf('weather:summary:%s', $normalizedName);

        $cached = $this->redis->get($key);

        if ($cached !== false) {
            /** @var array{
             *   city:string,
             *   current:float,
             *   average:?float,
             *   trend_direction:string,
             *   trend_delta:float
             * } $data
             */
            $data = \json_decode($cached, true, 512, JSON_THROW_ON_ERROR);

            $city = new City($data['city']);
            $current = new Temperature($data['current']);
            $average = $data['average'] !== null ? new Temperature($data['average']) : null;
            $trend = new Trend($data['trend_direction'], $data['trend_delta']);

            return new WeatherSummary($city, $current, $average, $trend);
        }

        $summary = $this->inner->getSummaryForCity($cityName);

        $payload = [
            'city'            => (string) $summary->city(),
            'current'         => $summary->currentTemperature()->value(),
            'average'         => $summary->hasAverage()
                ? $summary->averageTemperature()?->value()
                : null,
            'trend_direction' => $summary->trend()->direction(),
            'trend_delta'     => $summary->trend()->deltaInCelsius(),
        ];

        $this->redis->setex(
            $key,
            $this->ttlSeconds,
            \json_encode($payload, JSON_THROW_ON_ERROR),
        );

        return $summary;
    }
}
