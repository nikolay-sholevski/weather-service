<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * Represents the time a measurement was taken.
 *
 * Wraps DateTimeImmutable to enforce immutability and domain semantics.
 */
final class MeasurementTime
{
    public function __construct(
        private readonly DateTimeImmutable $occurredAt,
    ) {
    }

    public static function fromDateTimeInterface(DateTimeInterface $dateTime): self
    {
        return new self(DateTimeImmutable::createFromInterface($dateTime));
    }

    public static function fromString(string $timeString): self
    {
        return new self(new DateTimeImmutable($timeString));
    }

    public function value(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function isBefore(self $other): bool
    {
        return $this->occurredAt < $other->occurredAt;
    }

    public function isAfter(self $other): bool
    {
        return $this->occurredAt > $other->occurredAt;
    }

    public function __toString(): string
    {
        return $this->occurredAt->format(DateTimeInterface::ATOM);
    }
}
