<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Represents a city name in a normalized form.
 *
 * Invariants:
 * - Name is trimmed.
 * - Name is stored in a consistent canonical form.
 */
final class City
{
    private string $name;

    public function __construct(string $name)
    {
        $trimmed = \trim($name);

        if ($trimmed === '') {
            throw new \InvalidArgumentException('City name cannot be empty.');
        }

        $this->name = $trimmed;
    }

    /**
     * Canonical value (can be used as key, slug, etc.).
     */
    public function value(): string
    {
        return $this->name;
    }

    public function equals(self $other): bool
    {
        return \mb_strtolower($this->name) === \mb_strtolower($other->name);
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
