<?php

namespace App\Service\Weather;

use App\Entity\WeatherMeasurement;

interface TrendCalculatorInterface
{
    /**
     * @param WeatherMeasurement[] $measurements
     */
    public function calculateAverage(array $measurements, float $fallback): float;

    /**
     * @param float $current
     * @param float $avg
     */
    public function calculateTrendSign(float $current, float $avg): string;
}
