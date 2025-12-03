<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Controllers;

use App\Application\Services\GetCityWeatherServiceInterface;
use App\Application\DTO\WeatherSummaryDto;
use App\Infrastructure\Symfony\Request\GetCityWeatherRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Inbound HTTP adapter that exposes the GetCityWeather use case.
 */
final class GetCityWeatherController
{
    public function __construct(
        private readonly GetCityWeatherServiceInterface $getCityWeatherService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    #[Route('/api/weather', name: 'api_get_city_weather', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $dto = new GetCityWeatherRequest();
        $dto->city = (string) $request->query->get('city');

        $violations = $this->validator->validate($dto);

        if (\count($violations) > 0) {
            $errors = [];

            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            return new JsonResponse(
                [
                    'message' => 'Invalid request data.',
                    'errors'  => $errors,
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // At this point, $dto->city is non-null and valid
        $summary = $this->getCityWeatherService->getSummaryForCity($dto->city);

        // Map domain WeatherSummary -> DTO -> array for JSON
        $summaryDto = WeatherSummaryDto::fromDomain($summary);
        $data = $summaryDto->toArray();
        
        return new JsonResponse($data);
    }
}

