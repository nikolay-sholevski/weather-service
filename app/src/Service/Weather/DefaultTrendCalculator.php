<?php
declare(strict_types=1);wq

namespace App\Service\Weather;

class DefaultTrendCalculator implements TrendCalculatorInterface
{
    public const TREND_HOT    = '+';
    public const TREND_COLD   = '-';
    public const TREND_STABLE = '=';

    public function calculateAverage(array $measurements, float $fallback): float
    {
        if ($measurements === []) {
            return $fallback;
        }

        $sum = 0.0;
        foreach ($measurements as $m) {
            $sum += $m->getTemperature();
        }

        return $sum / count($measurements);
    }

    public function calculateTrendSign(float $current, float $avg): string
    {
        $diff    = $current - $avg;
        $epsilon = 0.5;

        if ($diff > $epsilon) {
            return self::TREND_HOT;
        }

        if ($diff < -$epsilon) {
            return self::TREND_COLD;
        }

        return self::TREND_STABLE;
    }
}
