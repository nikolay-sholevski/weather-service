<?php
declare(strict_types=1);

namespace App\Service\Weather;

interface WeatherProviderInterface
{
    public function getCurrentTemperature(string $city): float;
}
