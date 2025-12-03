<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

/**
 * Represents the temperature trend relative to some historical baseline.
 *
 * Direction describes the qualitative trend, deltaInCelsius is the numeric difference
 * between current temperature and the baseline (e.g. historical average).
 */
final class Trend
{
    public const DIRECTION_HOTTER = 'hotter';
    public const DIRECTION_COLDER = 'colder';
    public const DIRECTION_STABLE = 'stable';

    public function __construct(
        private readonly string $direction,
        private readonly float $deltaInCelsius
    ) {
        if (!\in_array($direction, [
            self::DIRECTION_HOTTER,
            self::DIRECTION_COLDER,
            self::DIRECTION_STABLE,
        ], true)) {
            throw new \InvalidArgumentException(\sprintf('Invalid trend direction: %s', $direction));
        }

        if (\is_nan($deltaInCelsius) || \is_infinite($deltaInCelsius)) {
            throw new \InvalidArgumentException('Delta must be a finite numeric value.');
        }
    }

    public function direction(): string
    {
        return $this->direction;
    }

    /**
     * The signed difference between current temperature and baseline (e.g. average).
     *
     * Positive delta means hotter than baseline, negative means colder.
     */
    public function deltaInCelsius(): float
    {
        return $this->deltaInCelsius;
    }

    public function isHotter(): bool
    {
        return $this->direction === self::DIRECTION_HOTTER;
    }

    public function isColder(): bool
    {
        return $this->direction === self::DIRECTION_COLDER;
    }

    public function isStable(): bool
    {
        return $this->direction === self::DIRECTION_STABLE;
    }

    /**
     * Human-friendly label that can be used in responses, logs, etc.
     */
    public function label(): string
    {
        if ($this->isStable()) {
            return 'stable';
        }

        return \sprintf(
            '%s by %.1f°C',
            $this->isHotter() ? 'hotter' : 'colder',
            \abs($this->deltaInCelsius)
        );
    }

    public function __toString(): string
    {
        return $this->label();
    }
}

