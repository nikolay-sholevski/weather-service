<?php

namespace App\Tests\Service\Weather;

use App\Service\Weather\ThirdPartyWeatherProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ThirdPartyWeatherProviderTest extends TestCase
{
    public function testGetCurrentTemperatureCallsHttpClientAndParsesResponse(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response   = $this->createMock(ResponseInterface::class);
        $logger     = $this->createMock(LoggerInterface::class);

        $httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://api.example.com/weather',
                [
                    'query' => [
                        'city'   => 'sofia',
                        'apiKey' => 'secret-key',
                    ],
                ],
            )
            ->willReturn($response);

        $response->expects($this->once())
            ->method('toArray')
            ->willReturn(['temperature' => 4]);

        $provider = new ThirdPartyWeatherProvider(
            $httpClient,
            $logger,
            'https://api.example.com',
            'secret-key',
        );

        $temp = $provider->getCurrentTemperature('sofia');

        $this->assertSame(4.0, $temp);
    }
}
