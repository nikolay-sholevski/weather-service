<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

final class ApiContext implements Context
{
    private KernelBrowser $client;
    private ?Response $response = null;

    public function __construct(KernelInterface $kernel)
    {
        $this->client = new KernelBrowser($kernel);
    }

    /**
     * @When I request the city weather for :city
     */
    public function iRequestTheCityWeatherFor(string $city): void
    {
        $this->client->request('GET', '/api/weather', [
            'city' => $city,
        ]);

        $this->response = $this->client->getResponse();
    }

    /**
     * @When I request the city weather with no parameters
     */
    public function iRequestTheCityWeatherWithNoParameters(): void
    {
        $this->client->request('GET', '/api/weather');
        $this->response = $this->client->getResponse();
    }

    /**
     * @Then the response status code should be :code
     */
    public function theResponseStatusCodeShouldBe(int $code): void
    {
        Assert::assertNotNull($this->response, 'No response was received yet.');
        Assert::assertSame($code, $this->response->getStatusCode());
    }

    /**
     * @Then the JSON response should contain JSON with at least:
     */
    public function theJsonResponseShouldContainJsonWithAtLeast(PyStringNode $expectedJson): void
    {
        Assert::assertNotNull($this->response, 'No response was received yet.');

        $content  = (string) $this->response->getContent();
        $actual   = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        $expected = json_decode($expectedJson->getRaw(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertIsArray($actual, 'Actual response is not a JSON object');
        Assert::assertIsArray($expected, 'Expected JSON is not a JSON object');

        foreach ($expected as $key => $value) {
            Assert::assertArrayHasKey($key, $actual, \sprintf(
                'Expected JSON key "%s" not found in response',
                $key
            ));

            if (\is_array($value)) {
                Assert::assertIsArray($actual[$key]);
                Assert::assertEquals($value, $actual[$key]);
            } else {
                Assert::assertSame($value, $actual[$key]);
            }
        }
    }

    /**
     * @Then the JSON response should contain exactly:
     */
    public function theJsonResponseShouldContainExactly(PyStringNode $expectedJson): void
    {
        Assert::assertNotNull($this->response, 'No response received');

        $content  = (string) $this->response->getContent();
        $actual   = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        $expected = json_decode($expectedJson->getRaw(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(
            $expected,
            $actual,
            'JSON response does not match expected structure'
        );
    }

    /**
     * Helper: get value by dot-notated path, e.g. "trend.delta"
     *
     * @param array<string,mixed> $data
     */
    private function getValueByPath(array $data, string $path): mixed
    {
        $parts = explode('.', $path);

        foreach ($parts as $part) {
            Assert::assertIsArray(
                $data,
                'Current JSON node is not an array while looking for ' . $path
            );
            Assert::assertArrayHasKey(
                $part,
                $data,
                sprintf('Key "%s" not found in JSON path "%s"', $part, $path)
            );

            /** @var mixed $data */
            $data = $data[$part];
        }

        return $data;
    }

    /**
     * @Then the JSON response should have field :path
     */
    public function theJsonResponseShouldHaveField(string $path): void
    {
        Assert::assertNotNull($this->response, 'No response received');

        $content = (string) $this->response->getContent();
        $actual  = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->getValueByPath($actual, $path);
    }

    /**
     * @Then the JSON response should have numeric field :path
     */
    public function theJsonResponseShouldHaveNumericField(string $path): void
    {
        Assert::assertNotNull($this->response, 'No response received');

        $content = (string) $this->response->getContent();
        $actual  = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $value = $this->getValueByPath($actual, $path);

        Assert::assertTrue(
            \is_int($value) || \is_float($value),
            sprintf('Field "%s" is not numeric (got: %s)', $path, gettype($value))
        );
    }

    /**
     * @Then the JSON response should have string field :path
     */
    public function theJsonResponseShouldHaveStringField(string $path): void
    {
        Assert::assertNotNull($this->response, 'No response received');

        $content = (string) $this->response->getContent();
        $actual  = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $value = $this->getValueByPath($actual, $path);

        Assert::assertIsString(
            $value,
            sprintf('Field "%s" is not a string (got: %s)', $path, gettype($value))
        );
    }
}

