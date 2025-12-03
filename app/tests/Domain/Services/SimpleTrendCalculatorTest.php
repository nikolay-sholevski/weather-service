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
        self::assertEquals(12.0, $average->value());

        $trend = $analysis->trend();

        // 20 - 12 = 8°C -> hotter
        self::assertSame(Trend::DIRECTION_HOTTER, $trend->direction());
        self::assertEquals(8.0, $trend->deltaInCelsius());
        self::assertTrue($trend->isHotter());
        self::assertFalse($trend->isStable());
    }

    public function testAnalyzeDetectsColderTrendWhenCurrentBelowAverage(): void
    {
        $calculator = new SimpleTrendCalculator(stableThresholdCelsius: 0.3);

        $city = new City('Burgas');

        $history = [
            new WeatherMeasurement(
                id: null,
                city: $city,
                temperature: new Temperature(18.0),
                measurementTime: new MeasurementTime(new \DateTimeImmutable('2024-02-01T00:00:00Z')),
            ),
            new WeatherMeasurement(
                id: null,
                city: $city,
                temperature: new Temperature(20.0),
                measurementTime: new MeasurementTime(new \DateTimeImmutable('2024-02-02T00:00:00Z')),
            ),
            new WeatherMeasurement(
                id: null,
                city: $city,
                temperature: new Temperature(22.0),
                measurementTime: new MeasurementTime(new \DateTimeImmutable('2024-02-03T00:00:00Z')),
            ),
        ];

        // average = (18 + 20 + 22) / 3 = 20.0
        $current = new Temperature(18.0);

        $analysis = $calculator->analyze($current, $history);

        self::assertTrue($analysis->hasAverage());
        $average = $analysis->averageTemperature();
        self::assertNotNull($average);
        self::assertEquals(20.0, $average->value());

        $trend = $analysis->trend();

        // 18 - 20 = -2°C -> colder
        self::assertSame(Trend::DIRECTION_COLDER, $trend->direction());
        self::assertEquals(-2.0, $trend->deltaInCelsius());
        self::assertTrue($trend->isColder());
        self::assertFalse($trend->isStable());
    }

    public function testAnalyzeReturnsStableTrendWhenDeltaWithinThreshold(): void
    {
        $calculator = new SimpleTrendCalculator(stableThresholdCelsius: 0.5);

        $city = new City('Varna');

        $history = [
            new WeatherMeasurement(
                id: null,
                city: $city,
                temperature: new Temperature(20.0),
                measurementTime: new MeasurementTime(new \DateTimeImmutable('2024-03-01T00:00:00Z')),
            ),
            new WeatherMeasurement(
                id: null,
                city: $city,
                temperature: new Temperature(20.0),
                measurementTime: new MeasurementTime(new \DateTimeImmutable('2024-03-02T00:00:00Z')),
            ),
            new WeatherMeasurement(
                id: null,
                city: $city,
                temperature: new Temperature(20.0),
                measurementTime: new MeasurementTime(new \DateTimeImmutable('2024-03-03T00:00:00Z')),
            ),
        ];

        // average ~ 20.0, current = 20.2, delta = +0.2 < 0.5 -> stable
        $current = new Temperature(20.2);

        $analysis = $calculator->analyze($current, $history);

        self::assertTrue($analysis->hasAverage());
        $average = $analysis->averageTemperature();
        self::assertNotNull($average);
        self::assertEquals(20.0, $average->value());

        $trend = $analysis->trend();

        self::assertSame(Trend::DIRECTION_STABLE, $trend->direction());
        self::assertTrue($trend->isStable());
        self::assertFalse($trend->isHotter());
        self::assertFalse($trend->isColder());
    }
}
