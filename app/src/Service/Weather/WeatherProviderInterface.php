<?php

namespace App\Service\Weather;

interface WeatherProviderInterface
{
    public function getCurrentTemperature(string $city): float;
}
