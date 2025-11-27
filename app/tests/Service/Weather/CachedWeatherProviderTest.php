<?php

namespace App\Tests\Service\Weather;

use App\Entity\WeatherMeasurement;
use App\Repository\WeatherMeasurementRepository;
use App\Service\Weather\CachedWeatherProvider;
use App\Service\Weather\WeatherProviderInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CachedWeatherProviderTest extends TestCase
{
    public function testReturnsCachedTemperatureWhenNotExpired(): void
    {
        $innerProvider = $this->createMock(WeatherProviderInterface::class);
        $repository    = $this->createMock(WeatherMeasurementRepository::class);
        $logger        = $this->createMock(LoggerInterface::class);

        $ttlSeconds = 3600;
        $city = 'sofia';

        $measurement = (new WeatherMeasurement())
            ->setCity($city)
            ->setTemperature(4.0)
            ->setFetchedAt(new DateTimeImmutable('-10 minutes'));

        $repository
            ->expects($this->once())
            ->method('findLatestForCity')
            ->with($city)
            ->willReturn($measurement);

        $innerProvider
            ->expects($this->never())
            ->method('getCurrentTemperature');

        $logger->expects($this->atLeastOnce())
            ->method('info');

        $repository
            ->expects($this->never())
            ->method('save');

        $provider = new CachedWeatherProvider(
            $innerProvider,
            $repository,
            $logger,
            $ttlSeconds,
        );

        $temp = $provider->getCurrentTemperature($city);

        $this->assertSame(4.0, $temp);
    }

    public function testCallsInnerProviderWhenCacheExpired(): void
    {
        $innerProvider = $this->createMock(WeatherProviderInterface::class);
        $repository    = $this->createMock(WeatherMeasurementRepository::class);
        $logger        = $this->createMock(LoggerInterface::class);

        $ttlSeconds = 3600;
        $city = 'sofia';

        $expiredMeasurement = (new WeatherMeasurement())
            ->setCity($city)
            ->setTemperature(4.0)
            ->setFetchedAt(new DateTimeImmutable('-2 hours'));

        $repository
            ->expects($this->once())
            ->method('findLatestForCity')
            ->with($city)
            ->willReturn($expiredMeasurement);

        $innerProvider
            ->expects($this->once())
            ->method('getCurrentTemperature')
            ->with($city)
            ->willReturn(7.5);

        $repository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function (WeatherMeasurement $m) use ($city) {
                    $this->assertSame($city, $m->getCity());
                    $this->assertSame(7.5, $m->getTemperature());
                    $this->assertInstanceOf(DateTimeImmutable::class, $m->getFetchedAt());

                    return true;
                }),
                true,
            );

        $logger->expects($this->atLeastOnce())
            ->method('info');

        $provider = new CachedWeatherProvider(
            $innerProvider,
            $repository,
            $logger,
            $ttlSeconds,
        );

        $temp = $provider->getCurrentTemperature($city);

        $this->assertSame(7.5, $temp);
    }

    public function testCallsInnerProviderWhenNoCachedMeasurement(): void
    {
        $innerProvider = $this->createMock(WeatherProviderInterface::class);
        $repository    = $this->createMock(WeatherMeasurementRepository::class);
        $logger        = $this->createMock(LoggerInterface::class);

        $ttlSeconds = 3600;
        $city = 'sofia';

        $repository
            ->expects($this->once())
            ->method('findLatestForCity')
            ->with($city)
            ->willReturn(null);

        $innerProvider
            ->expects($this->once())
            ->method('getCurrentTemperature')
            ->with($city)
            ->willReturn(2.5);

        $repository
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function (WeatherMeasurement $m) use ($city) {
                    $this->assertSame($city, $m->getCity());
                    $this->assertSame(2.5, $m->getTemperature());

                    return true;
                }),
                true,
            );

        $logger->expects($this->atLeastOnce())
            ->method('info');

        $provider = new CachedWeatherProvider(
            $innerProvider,
            $repository,
            $logger,
            $ttlSeconds,
        );

        $temp = $provider->getCurrentTemperature($city);

        $this->assertSame(2.5, $temp);
    }
}
