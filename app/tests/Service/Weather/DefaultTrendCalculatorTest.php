<?php

namespace App\Tests\Service\Weather;

use App\Entity\WeatherMeasurement;
use App\Service\Weather\DefaultTrendCalculator;
use PHPUnit\Framework\TestCase;

class DefaultTrendCalculatorTest extends TestCase
{
    public function testCalculateAverageUsesFallbackWhenNoMeasurements(): void
    {
        $calculator = new DefaultTrendCalculator();
        $result = $calculator->calculateAverage([], 5.0);

        $this->assertSame(5.0, $result);
    }

    public function testCalculateAverageComputesCorrectMean(): void
    {
        $calculator = new DefaultTrendCalculator();

        $m1 = (new WeatherMeasurement())->setTemperature(2.0);
        $m2 = (new WeatherMeasurement())->setTemperature(4.0);
        $m3 = (new WeatherMeasurement())->setTemperature(6.0);

        $result = $calculator->calculateAverage([$m1, $m2, $m3], 0.0);

        $this->assertSame(4.0, $result);
    }

    public function testCalculateTrendSignHot(): void
    {
        $calculator = new DefaultTrendCalculator();
        $this->assertSame('+', $calculator->calculateTrendSign(6.0, 4.0));
    }

    public function testCalculateTrendSignCold(): void
    {
        $calculator = new DefaultTrendCalculator();
        $this->assertSame('-', $calculator->calculateTrendSign(2.0, 4.0));
    }

    public function testCalculateTrendSignNeutral(): void
    {
        $calculator = new DefaultTrendCalculator();
        $this->assertSame('=', $calculator->calculateTrendSign(4.2, 4.0));
    }
}
