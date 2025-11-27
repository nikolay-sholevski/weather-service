<?php

namespace App\Tests\Controller;

use App\Controller\WeatherController;
use App\Service\Weather\WeatherResult;
use App\Service\Weather\WeatherService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class WeatherControllerTest extends TestCase
{
    public function testCityReturnsExpectedJson(): void
    {
        $weatherService = $this->createMock(WeatherService::class);

        $weatherService
            ->method('getCityWeather')
            ->with('sofia')
            ->willReturn(new WeatherResult(
                city: 'sofia',
                temperature: 4.0,
                trendSign: '+',
                value: '4.0 +',
            ));

        $controller = new WeatherController($weatherService);

        $response = $controller->city('sofia');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $content = (string) $response->getContent();

        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('sofia', $data['city']);
        $this->assertSame(4.0, (float) $data['temperature']);
        $this->assertSame('+', $data['trend']);
        $this->assertSame('4.0 +', $data['value']);
    }
}
