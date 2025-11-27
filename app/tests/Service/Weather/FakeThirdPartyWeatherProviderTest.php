<?php

// tests/Service/Weather/FakeThirdPartyWeatherProviderTest.php

namespace App\Tests\Service\Weather;

use App\Service\Weather\FakeThirdPartyWeatherProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FakeThirdPartyWeatherProviderTest extends TestCase
{
    public function testReturnsConfiguredTemperatureCaseInsensitive(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $provider = new FakeThirdPartyWeatherProvider($logger, [
            'sofia' => 4.0,
        ]);

        $temp1 = $provider->getCurrentTemperature('Sofia');
        $temp2 = $provider->getCurrentTemperature('sofia');

        $this->assertSame(4.0, $temp1);
        $this->assertSame(4.0, $temp2);
        $this->assertSame(2, $provider->getCallCount());
    }

    public function testSetTemperatureOverridesExisting(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $provider = new FakeThirdPartyWeatherProvider($logger, [
            'sofia' => 4.0,
        ]);

        $provider->setTemperature('SOFIA', 10.5);
        $temp = $provider->getCurrentTemperature('sofia');

        $this->assertSame(10.5, $temp);
        $this->assertSame(1, $provider->getCallCount());
    }

    public function testThrowsIfTemperatureNotConfigured(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $provider = new FakeThirdPartyWeatherProvider($logger);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("No fake temperature set for 'Sofia'");

        $provider->getCurrentTemperature('Sofia');
    }
}
