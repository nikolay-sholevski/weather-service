<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Represents a temperature in degrees Celsius.
 *
 * This VO encapsulates domain logic for comparing temperatures.
 */
final class Temperature
{
    public function __construct(
        private readonly float $valueInCelsius
    ) {
        if (\is_nan($valueInCelsius) || \is_infinite($valueInCelsius)) {
            throw new \InvalidArgumentException('Temperature must be a finite numeric value.');
        }
    }

    public function value(): float
    {
        return $this->valueInCelsius;
    }

    public function isAbove(self $other): bool
    {
        return $this->valueInCelsius > $other->valueInCelsius;
    }

    public function isBelow(self $other): bool
    {
        return $this->valueInCelsius < $other->valueInCelsius;
    }

    public function difference(self $other): float
    {
        return $this->valueInCelsius - $other->valueInCelsius;
    }

    public function equals(self $other, float $epsilon = 0.0001): bool
    {
        return \abs($this->difference($other)) < $epsilon;
    }

    public function __toString(): string
    {
        return \sprintf('%.1f°C', $this->valueInCelsius);
    }
}

