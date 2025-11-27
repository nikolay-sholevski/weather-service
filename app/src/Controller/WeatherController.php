<?php

namespace App\Controller;

use App\Service\Weather\WeatherService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class WeatherController
{
    public function __construct(
        private readonly WeatherService $weatherService,
    ) {
    }

    #[Route('/weather/{city}', name: 'app_weather', methods: ['GET'])]
    public function city(string $city): JsonResponse
    {
        $weather = $this->weatherService->getCityWeather($city);

        return new JsonResponse([
            'city'        => $weather->city,
            'temperature' => $weather->temperature,
            'trend'       => $weather->trendSign,
            'value'       => $weather->value,
        ]);
    }
}
