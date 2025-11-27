<?php

namespace App\Tests\Service\Weather;

use App\Entity\WeatherMeasurement;
use App\Repository\WeatherMeasurementRepository;
use App\Service\Weather\HourlyWeatherFetcher;
use App\Service\Weather\WeatherProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class HourlyWeatherFetcherTest extends TestCase
{
    public function testRefreshCityFetchesAndStoresMeasurement(): void
    {
        $provider = $this->createMock(WeatherProviderInterface::class);
        $repository = $this->createMock(WeatherMeasurementRepository::class);
        $logger = $this->createMock(LoggerInterface::class);

        $provider->expects($this->once())
            ->method('getCurrentTemperature')
            ->with('sofia')
            ->willReturn(7.5);

        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (WeatherMeasurement $m) {
                $this->assertSame('sofia', $m->getCity());
                $this->assertSame(7.5, $m->getTemperature());
                $this->assertInstanceOf(\DateTimeImmutable::class, $m->getFetchedAt());

                return true;
            }));

        $fetcher = new HourlyWeatherFetcher($provider, $repository, $logger);

        $fetcher->refreshCity('Sofia');
    }

    public function testRefreshManyCallsRefreshCityForEachCity(): void
    {
        $provider = $this->createMock(WeatherProviderInterface::class);
        $repository = $this->createMock(WeatherMeasurementRepository::class);
        $logger = $this->createMock(LoggerInterface::class);

        $provider->expects($this->exactly(2))
            ->method('getCurrentTemperature')
            ->willReturnOnConsecutiveCalls(1.0, 2.0);

        $repository->expects($this->exactly(2))
            ->method('save')
            ->with($this->isInstanceOf(WeatherMeasurement::class));

        $fetcher = new HourlyWeatherFetcher($provider, $repository, $logger);

        $fetcher->refreshMany(['Sofia', 'Plovdiv']);
    }
}
