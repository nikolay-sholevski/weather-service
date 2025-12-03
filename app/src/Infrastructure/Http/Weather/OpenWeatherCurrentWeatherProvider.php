<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Weather;

use App\Application\Ports\CurrentWeatherProviderInterface;
use App\Domain\ValueObjects\City;
use App\Domain\ValueObjects\Temperature;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * HTTP adapter for fetching current weather from an external API.
 *
 * This is an outbound adapter implementing CurrentWeatherProviderInterface.
 */
final class OpenWeatherCurrentWeatherProvider implements CurrentWeatherProviderInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://api.openweathermap.org/data/2.5/weather',
    ) {
    }

    public function getCurrentTemperature(City $city): Temperature
    {
        try {
            $response = $this->httpClient->request('GET', $this->baseUrl, [
                'query' => [
                    'q'     => $city->value(),
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                $this->logger->warning('Non-200 response from weather API', [
                    'status_code' => $statusCode,
                    'city'        => $city->value(),
                ]);

                // Fallback or domain-specific error handling can go here
            }

            /** @var array{ main?: array{ temp?: float } } $data */
            $data = $response->toArray();

            $tempValue = $data['main']['temp'] ?? null;

            if ($tempValue === null) {
                throw new \RuntimeException('Temperature not found in provider response.');
            }

            return new Temperature((float) $tempValue);
        } catch (\Throwable $e) {
            $this->logger->error('Error fetching current temperature', [
                'exception' => $e,
                'city'      => $city->value(),
            ]);

            throw $e;
        }
    }
}

