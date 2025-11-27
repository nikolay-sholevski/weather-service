# Weather Service -- Assignment Solution

This project implements a demo weather service prototype 

------------------------------------------------------------------------

## Features

### `NOTE` : 
- TODO: ThirdPartyWeatherProvider service should be adapted according to whatever external provider is chosen

### 1. `/weather/{city}` API

Returns: - `city` (normalized) - `temperature` - `trend` (`+`, `-`,
`=`) - `value` (`{temp} {trend}`)

### 2. Caching Layer

`CachedWeatherProvider` wraps the real external provider:

-   Cache HIT → return recent temperature (\< 1 hour old)
-   Cache MISS → fetch from provider and store in DB

Ensures reduced API calls and consistent data.

### 3. Hourly Background Fetcher

`HourlyWeatherFetcher` periodically:

-   Fetches from the **uncached** real provider\
-   Stores fresh measurement for historical tracking

Console command:

    php bin/console app:weather:refresh

### 4. Trend Calculation

`DefaultTrendCalculator` compares:

    current temperature vs average of last 10 measurements

Trend signs: - `+` hotter - `-` colder - `=` stable (±0.5)

### 5. Tests & Static Analysis

-   Full PHPUnit test coverage\
-   PHPStan level **8** clean\
-   Strict types and interfaces everywhere

Run tests:

    php bin/phpunit

Run static analysis:

    vendor/bin/phpstan analyse

------------------------------------------------------------------------

## Bonus: UI Demo

A small visual demo page is available at:

    /weather

Features: - Cloudy sky background (SVG) - Animated emoji showing trend -
Centered card with gradient + soft shadows - Temperature displayed with
auto-prefix (+7 °C, 0 °C, -3 °C) - Uses vanilla JS & Twig to call the
backend API

### Screenshot

`<img src="docs/weather-demo.png" width="600">`{=html}

------------------------------------------------------------------------

## Setup

### 1. Build & start the containers

    docker-compose up --build -d

### 2. Install dependencies inside the PHP container

    docker exec -it weather-app bash
    composer install

(Optional) Run migrations:

    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate

### 3. Access the application

    http://localhost:8080

The weather API:

    http://localhost:8080/weather/{city}

The demo UI:

    http://localhost:8080/weather

------------------------------------------------------------------------

## Project Structure

    src/
     ├── Command/
     │     └── FetchHourlyWeatherCommand.php
     ├── Controller/
     │     └── WeatherController.php
     ├── Entity/
     │     └── WeatherMeasurement.php
     ├── Repository/
     │     └── WeatherMeasurementRepository.php
     └── Service/
           └── Weather/
                 ├── CachedWeatherProvider.php
                 ├── ThirdPartyWeatherProvider.php
                 ├── FakeThirdPartyWeatherProvider.php
                 ├── WeatherService.php
                 ├── HourlyWeatherFetcher.php
                 ├── WeatherProviderInterface.php
                 ├── DefaultTrendCalculator.php
                 └── TrendCalculatorInterface.php

    tests/
     ├── Controller/
     │     └── WeatherControllerTest.php
     └── Service/
           └── Weather/
                 ├── CachedWeatherProviderTest.php
                 ├── ThirdPartyWeatherProviderTest.php
                 ├── FakeThirdPartyWeatherProviderTest.php
                 ├── DefaultTrendCalculatorTest.php
                 ├── HourlyWeatherFetcherTest.php
                 └── WeatherServiceTest.php

------------------------------------------------------------------------

## Notes

-   Architecture is decoupled and test-friendly.
-   Caching and external provider access follow decorator pattern.
-   Trend calculation isolated in a separate service.
-   UI demo built intentionally lightweight using Twig + basic JS.
-   PHPStan level 8 enforces strict correctness and type safety.

------------------------------------------------------------------------

