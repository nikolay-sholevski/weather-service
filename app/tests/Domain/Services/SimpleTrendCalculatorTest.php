<?php

declare(strict_types=1);

namespace App\Tests\Domain\Services;

use App\Domain\Entities\WeatherMeasurement;
use App\Domain\Services\SimpleTrendCalculator;
use App\Domain\ValueObjects\City;
use App\Domain\ValueObjects\MeasurementTime;
use App\Domain\ValueObjects\Temperature;
use App\Domain\ValueObjects\Trend;
use PHPUnit\Framework\TestCase;

final class SimpleTrendCalculatorTest extends TestCase
{
    public function testAnalyzeReturnsStableTrendWhenNoHistoricalMeasurements(): void
    {
        $calculator = new SimpleTrendCalculator(stableThresholdCelsius: 0.3);

        $current = new Temperature(20.0);

        $analysis = $calculator->analyze($current, []);

        $trend = $analysis->trend();

        // average should be null when there is no history
        self::assertFalse($analysis->hasAverage());
        self::assertNull($analysis->averageTemperature());

        // trend should be stable with zero delta
        self::assertSame(Trend::DIRECTION_STABLE, $trend->direction());
        self::assertSame(0.0, $trend->deltaInCelsius());
        self::assertTrue($trend->isStable());
    }

    public function testAnalyzeDetectsHotterTrendWhenCurrentAboveAverage(): void
    {
        $calculator = new SimpleTrendCalculator(stableThresholdCelsius: 0.3);

        $city = new City('Sofia');

        $history = [
            new WeatherMeasurement(
                id: null,
                city: $city,
                temperature: new Temperature(10.0),
                measurementTime: new MeasurementTime(new \DateTimeImmutable('2024-01-01T00:00:00Z')),
            ),
            new WeatherMeasurement(
                id: null,
                city: $city,
                temperature: new Temperature(12.0),
                measurementTime: new MeasurementTime(new \DateTimeImmutable('2024-01-02T00:00:00Z')),
            ),
            new WeatherMeasurement(
                id: null,
                city: $city,
                temperature: new Temperature(14.0),
                measurementTime: new MeasurementTime(new \DateTimeImmutable('2024-01-03T00:00:00Z')),
            ),
        ];

        $current = new Temperature(20.0);

        $analysis = $calculator->analyze($current, $history);

        self::assertTrue($analysis->hasAverage());
        $average = $analysis->averageTemperature();
        self::assertNotNull($average);

        // (10 + 12 + 14) / 3 = 12.0
        self::assertEquals(12.0, $average->value(), '', 0.0001);

        $trend = $analysis->trend();

        // 20 - 12 = 8°C -> hotter
        self::assertSame(Trend::DIRECTION_HOTTER, $trend->direction());
        self::assertEquals(8.0, $trend->deltaInCelsius(), '', 0.0001);
        self::assertTrue($trend->isHotter());
        self::assertFalse($trend->isStable());
    }
}

