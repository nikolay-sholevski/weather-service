<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObjects;

use App\Domain\ValueObjects\Trend;
use PHPUnit\Framework\TestCase;

final class TrendTest extends TestCase
{
    public function testHotterTrendHelpersWorkCorrectly(): void
    {
        $trend = new Trend(Trend::DIRECTION_HOTTER, 3.5);

        self::assertSame(Trend::DIRECTION_HOTTER, $trend->direction());
        self::assertSame(3.5, $trend->deltaInCelsius());

        self::assertTrue($trend->isHotter());
        self::assertFalse($trend->isColder());
        self::assertFalse($trend->isStable());

        $label = $trend->label();
        self::assertStringContainsString('hotter', $label);
    }

    public function testColderTrendHelpersWorkCorrectly(): void
    {
        $trend = new Trend(Trend::DIRECTION_COLDER, -2.0);

        self::assertSame(Trend::DIRECTION_COLDER, $trend->direction());
        self::assertSame(-2.0, $trend->deltaInCelsius());

        self::assertTrue($trend->isColder());
        self::assertFalse($trend->isHotter());
        self::assertFalse($trend->isStable());

        $label = $trend->label();
        self::assertStringContainsString('colder', $label);
    }

    public function testStableTrendHelpersWorkCorrectly(): void
    {
        $trend = new Trend(Trend::DIRECTION_STABLE, 0.0);

        self::assertSame(Trend::DIRECTION_STABLE, $trend->direction());
        self::assertSame(0.0, $trend->deltaInCelsius());

        self::assertTrue($trend->isStable());
        self::assertFalse($trend->isHotter());
        self::assertFalse($trend->isColder());

        $label = $trend->label();
        self::assertStringContainsString('stable', $label);
    }
}
