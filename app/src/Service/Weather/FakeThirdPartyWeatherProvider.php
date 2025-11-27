<?php
declare(strict_types=1);

namespace App\Service\Weather;

use Psr\Log\LoggerInterface;

class FakeThirdPartyWeatherProvider implements WeatherProviderInterface
{
    /** @var array<float> */
    private array $temperatures = [];

    private int $callCount = 0;

    /**
     * @param array<float> $initialTemperatures
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        array $initialTemperatures = [],
    ) {
        foreach ($initialTemperatures as $city => $temp) {
            $this->setTemperature($city, $temp);
        }
    }

    public function setTemperature(string $city, float $temp): void
    {
        $this->temperatures[mb_strtolower($city)] = $temp;
    }

    public function getCurrentTemperature(string $city): float
    {
        $key = mb_strtolower($city);

        $this->callCount++;
        $this->logger->info('[FakeProvider] Called for city', [
            'city' => $key,
            'call_count' => $this->callCount,
        ]);

        if (!isset($this->temperatures[$key])) {
            throw new \RuntimeException("No fake temperature set for '$city'");
        }

        return $this->temperatures[$key];
    }

    public function getCallCount(): int
    {
        return $this->callCount;
    }
}
