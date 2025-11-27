<?php

namespace App\Tests\Service\Weather;

use App\Entity\WeatherMeasurement;
use App\Repository\WeatherMeasurementRepository;
use App\Service\Weather\TrendCalculatorInterface;
use App\Service\Weather\WeatherProviderInterface;
use App\Service\Weather\WeatherResult;
use App\Service\Weather\WeatherService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class WeatherServiceTest extends TestCase
{
    public function testGetCityWeatherHappyPath(): void
    {
        $weatherProvider = $this->createMock(WeatherProviderInterface::class);
        $repository      = $this->createMock(WeatherMeasurementRepository::class);
        $trendCalculator = $this->createMock(TrendCalculatorInterface::class);
        $logger          = $this->createMock(LoggerInterface::class);

        $cityInput   = 'SoFiA';
        $cityNorm    = 'sofia';
        $currentTemp = 4.0;
        $avgTemp     = 3.5;
        $trendSign   = '+';

        $measurements = [
            $this->createWeatherMeasurement('sofia', 3.0),
            $this->createWeatherMeasurement('sofia', 4.0),
        ];

        $weatherProvider
            ->expects($this->once())
            ->method('getCurrentTemperature')
            ->with($cityNorm)
            ->willReturn($currentTemp);

        $repository
            ->expects($this->once())
            ->method('findLastNDaysForCity')
            ->with($cityNorm, 10)
            ->willReturn($measurements);

        $trendCalculator
            ->expects($this->once())
            ->method('calculateAverage')
            ->with($measurements, $currentTemp)
            ->willReturn($avgTemp);

        $trendCalculator
            ->expects($this->once())
            ->method('calculateTrendSign')
            ->with($currentTemp, $avgTemp)
            ->willReturn($trendSign);

        $service = new WeatherService(
            $weatherProvider,
            $repository,
            $trendCalculator,
            $logger,
        );

        $result = $service->getCityWeather($cityInput);

        $this->assertInstanceOf(WeatherResult::class, $result);
        $this->assertSame($cityNorm, $result->city);
        $this->assertSame($currentTemp, $result->temperature);
        $this->assertSame($trendSign, $result->trendSign);
        $this->assertSame(sprintf('%s %s', $currentTemp, $trendSign), $result->value);
    }

    private function createWeatherMeasurement(string $city, float $temperature): WeatherMeasurement
    {
        $m = new WeatherMeasurement();
        $m->setCity($city);
        $m->setTemperature($temperature);

        return $m;
    }
}
