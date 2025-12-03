<?php

declare(strict_types=1);

namespace App\Application\Ports;

use App\Domain\ValueObjects\City;
use App\Domain\ValueObjects\Temperature;

/**
 * Outbound port for obtaining the current temperature for a given city
 * from an external weather provider (HTTP API, etc.).
 */
interface CurrentWeatherProviderInterface
{
    public function getCurrentTemperature(City $city): Temperature;
}

