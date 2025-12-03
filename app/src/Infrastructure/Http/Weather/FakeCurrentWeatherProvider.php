<?php
declare(strict_types=1);

namespace App\Infrastructure\Http\Weather;

use App\Application\Ports\CurrentWeatherProviderInterface;
use App\Domain\ValueObjects\City;
use App\Domain\ValueObjects\Temperature;

final class FakeCurrentWeatherProvider implements CurrentWeatherProviderInterface
{
    public function getCurrentTemperature(City $city): Temperature
    {
        // Generate a random temperature in a realistic range
        $value = mt_rand(50, 300) / 10; 
        // 50 → 5.0°C, 300 → 30.0°C

        return new Temperature($value);
    }
}

