<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controllers;

use App\Application\Services\GetCityWeatherServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Inbound HTTP adapter that exposes the GetCityWeather use case.
 */
final class GetCityWeatherController
{
    public function __construct(
        private readonly GetCityWeatherServiceInterface $getCityWeatherService
    ) {
    }

    #[Route('/api/weather', name: 'api_get_city_weather', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $cityName = (string) $request->query->get('city', '');

        if (\trim($cityName) === '') {
            return new JsonResponse(
                ['error' => 'Query parameter "city" is required.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $summary = $this->getCityWeatherService->getSummaryForCity($cityName);

        $data = [
            'city'     => (string) $summary->city(),
            'current'  => $summary->currentTemperature()->value(),
            'average'  => $summary->hasAverage()
                ? $summary->averageTemperature()?->value()
                : null,
            'trend' => [
                'direction' => $summary->trend()->direction(),
                'delta'     => $summary->trend()->deltaInCelsius(),
                'label'     => $summary->trend()->label(),
            ],
        ];

        return new JsonResponse($data);
    }
}

