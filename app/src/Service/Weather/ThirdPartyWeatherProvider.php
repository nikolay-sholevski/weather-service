<?php
declare(strict_types=1);

namespace App\Service\Weather;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ThirdPartyWeatherProvider implements WeatherProviderInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
        private readonly string $apiBaseUrl,
        private readonly string $apiKey,
    ) {
    }

    public function getCurrentTemperature(string $city): float
    {
        $this->logger->info('[ThirdPartyWeatherProvider] Calling external weather API', ['city' => $city]);

        $response = $this->client->request('GET', $this->apiBaseUrl.'/weather', [
            'query' => [
                'city'   => $city,
                'apiKey' => $this->apiKey,
            ],
        ]);

        $data = $response->toArray();
        $temp = (float) $data['temperature'];

        $this->logger->info('[ThirdPartyWeatherProvider] External API response', [
            'city'        => $city,
            'temperature' => $temp,
        ]);

        return $temp;
    }
}
