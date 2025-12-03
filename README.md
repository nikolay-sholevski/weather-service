# Weather Service – Hexagonal Symfony 7 Microservice

## Overview

This project is a small **Symfony 7** microservice that exposes a single HTTP endpoint:

- `GET /api/weather?city=NAME`

It returns a **weather summary** for a city, including:

- current temperature
- historical average temperature
- a trend (getting hotter/colder and by how much)

The code is organised using **Domain‑Driven Design (DDD)** and **Hexagonal Architecture** with clear separation between:

- **Domain** – pure business logic (entities, value objects, domain services)
- **Application** – use cases, ports, DTOs
- **Infrastructure** – Symfony controllers, Doctrine adapters, HTTP clients, console commands, etc.

There is also a console command that imports current measurements for one or more cities and persists them as historical data.

## Tech Stack

- **PHP** >= 8.2
- **Symfony** 7.3 (FrameworkBundle, Console, HttpKernel, etc.)
- **Doctrine ORM** 3.x + Doctrine Migrations
- **PostgreSQL** (used via `DATABASE_URL`)
- **Redis** (optional, for weather cache)
- **Behat** + FriendsOfBehat SymfonyExtension for API/BDD tests
- **PHPUnit** for unit/integration tests (via `phpunit/phpunit` and `symfony/phpunit-bridge`)

---

## Project Structure

Key directories (in `src/`):

```text
src/
  Application/
    DTO/
      WeatherSummaryDto.php
    Ports/
      CurrentWeatherProviderInterface.php
      WeatherHistoryPortInterface.php
    Services/
      GetCityWeatherServiceInterface.php
      GetCityWeatherService.php
      ImportCityWeatherServiceInterface.php
      ImportCityWeatherService.php

  Domain/
    Entities/
      WeatherMeasurement.php
    ValueObjects/
      City.php
      Temperature.php
      MeasurementTime.php
      Trend.php
      TrendAnalysis.php
      WeatherSummary.php
    Services/
      TrendCalculatorInterface.php
      SimpleTrendCalculator.php

  Infrastructure/
    Http/
      Weather/
        FakeCurrentWeatherProvider.php
        OpenWeatherCurrentWeatherProvider.php
    Persistence/
      Doctrine/
        Repository/
          WeatherHistoryDoctrineAdapter.php
    Symfony/
      Request/
        GetCityWeatherRequest.php
      Controllers/
        GetCityWeatherController.php
      Command/
        ImportWeatherMeasurementsCommand.php

  Kernel.php
  Schedule.php
```

Other important paths:

- `config/` – standard Symfony configuration (routes, services, Doctrine, etc.)
- `migrations/` – Doctrine migrations for schema + seed data
- `features/` – Behat feature files
- `tests/Behat/` – Behat context classes
- `behat.yml` – Behat configuration

---

## Architecture

### Layers & Responsibilities

**Domain layer (`src/Domain`)**

- Contains pure domain objects and logic:
  - `WeatherMeasurement` (Entity)
  - `City`, `Temperature`, `MeasurementTime`, `Trend`, `WeatherSummary` (Value Objects)
  - `TrendCalculatorInterface` and `SimpleTrendCalculator` (Domain Service)
- Has **no dependency on Symfony / Doctrine / HTTP**.
- Knows nothing about persistence, controllers or external APIs.

**Application layer (`src/Application`)**

- Contains application services (**use cases**) and **ports**:
  - `GetCityWeatherService` (+ interface)
    - Orchestrates fetching current weather and historical data and building `WeatherSummary`.
  - `ImportCityWeatherService` (+ interface)
    - Imports current weather for a city and persists it as `WeatherMeasurement`.
- Uses **ports** to talk to the outside world:
  - `CurrentWeatherProviderInterface` – outbound port for fetching current temperature for a `City`.
  - `WeatherHistoryPortInterface` – outbound port for reading/writing `WeatherMeasurement` history.
- Uses domain model (entities, value objects, domain services) but knows nothing about Symfony or Doctrine.

**Infrastructure layer (`src/Infrastructure`)**

- Contains **adapters** that implement the ports and expose the application to the outside world:
  - **Outbound adapters:**
    - `OpenWeatherCurrentWeatherProvider` / `FakeCurrentWeatherProvider`
      - Implement `CurrentWeatherProviderInterface` and call external HTTP API (OpenWeather) or return fake data.
    - `WeatherHistoryDoctrineAdapter`
      - Implements `WeatherHistoryPortInterface` using Doctrine ORM and PostgreSQL.
  - **Inbound adapters:**
    - `GetCityWeatherController`
      - Symfony HTTP controller for `GET /api/weather`.
      - Accepts and validates `GetCityWeatherRequest`.
      - Calls `GetCityWeatherServiceInterface` and returns JSON using `WeatherSummaryDto`.
    - `ImportWeatherMeasurementsCommand`
      - Symfony console command `app:weather:import-measurement`.
      - Calls `ImportCityWeatherServiceInterface` for the given cities.
- Depends on Symfony, Doctrine, HTTP client, console, etc.

### Inbound vs Outbound Ports (Hexagonal)

- **Inbound (driving) side** – how the outside world calls us:
  - HTTP request → `GetCityWeatherController`
  - CLI command → `ImportWeatherMeasurementsCommand`

- **Outbound (driven) side** – how we depend on external systems:
  - `CurrentWeatherProviderInterface` → implemented by:
    - `OpenWeatherCurrentWeatherProvider` (real HTTP client)
    - `FakeCurrentWeatherProvider` (for dev/testing)
  - `WeatherHistoryPortInterface` → implemented by:
    - `WeatherHistoryDoctrineAdapter` (PostgreSQL via Doctrine)

### C4 Model (high-level)

#### System Context (C1)

- **System:** Weather Service (this project)
- **External Systems:**
  - OpenWeather API – provides live temperature data per city.
  - PostgreSQL – stores historical weather measurements.
  - Redis (optional) – caching for weather data.
- **Users/Clients:** Any HTTP client (front-end app, Postman, tests) calling `/api/weather`.

#### Container / Component (C2/C3) – Textual

- **Container:** Symfony 7 Application
  - **Components:**
    - Domain model (`WeatherMeasurement`, `WeatherSummary`, `Trend`, etc.)
    - Application services (`GetCityWeatherService`, `ImportCityWeatherService`)
    - HTTP API (controller for `/api/weather`)
    - Console command (`app:weather:import-measurement`)
    - Ports & adapters for:
      - HTTP weather provider (OpenWeather)
      - Persistence (Doctrine/PostgreSQL)

#### Optional: C4-style diagram (Mermaid)

```mermaid
flowchart LR
  Client[API Client
(Postman, Frontend, Behat)] -->|HTTP /api/weather| Controller[GetCityWeatherController]
  Controller -->|calls| GetCityWeatherService

  subgraph Application Layer
    GetCityWeatherService
    ImportCityWeatherService
  end

  GetCityWeatherService -->|uses| TrendCalculator[SimpleTrendCalculator]
  GetCityWeatherService -->|port| CurrentWeatherPort[CurrentWeatherProviderInterface]
  GetCityWeatherService -->|port| HistoryPort[WeatherHistoryPortInterface]

  subgraph Infrastructure Layer
    OpenWeatherAdapter[OpenWeatherCurrentWeatherProvider]
    FakeWeatherAdapter[FakeCurrentWeatherProvider]
    DoctrineAdapter[WeatherHistoryDoctrineAdapter]
    ImportCommand[ImportWeatherMeasurementsCommand]
  end

  CurrentWeatherPort --> OpenWeatherAdapter
  HistoryPort --> DoctrineAdapter

  OpenWeatherAdapter -->|HTTP| OpenWeather[OpenWeather API]
  DoctrineAdapter -->|SQL| Postgres[(PostgreSQL)]
```

---

## Setup & Installation

### Requirements

- PHP >= 8.2
- Composer
- PostgreSQL 16 (or compatible)
- (Optional) Redis (for caching)
- Git / Docker (optional, depending on environment)

### 1. Install PHP dependencies

From the project root:

```bash
composer install
```

### 2. Configure environment variables

Copy `.env` to `.env.local` if needed, and configure your own settings.

Important variables:

```env
APP_ENV=dev
APP_DEBUG=1

DATABASE_URL="postgresql://symfony:symfony@weather-db:5432/weather?serverVersion=16&charset=utf8"

OPENWEATHER_API_KEY=your_own_api_key_here
OPENWEATHER_BASE_URL="https://api.openweathermap.org/data/2.5"

REDIS_HOST=redis
REDIS_PORT=6379
WEATHER_CACHE_TTL_SECONDS=300
```

For tests, `.env.test` overrides are used (already present in the project).

### 3. Database setup (dev)

Create the database:

```bash
php bin/console doctrine:database:create
```

Run migrations:

```bash
php bin/console doctrine:migrations:migrate
```

Existing migrations:

- `Version20250201CreateWeatherMeasurements`  
  – creates the `weather_measurements` table.
- `Version20250201SeedInitialWeatherData`  
  – seeds some initial measurements (e.g. for Sofia) for trend calculations.

You can re-run them on a fresh DB to get the correct schema and sample data.

---

## Running the Application

This project is structured as a Symfony microservice and is primarily exercised via:

- HTTP requests (e.g. through functional tests like Behat)
- console commands

Depending on your environment, you can run it:

- Under a web server pointing to your front controller (if you add `public/index.php`), or
- Using Symfony’s local server (if installed globally):

```bash
symfony server:start
```

For the purpose of this task, the main interaction is through tests and the console.

---

## HTTP API: `/api/weather`

### Endpoint

`GET /api/weather?city=NAME`

### Request

- **Query parameters:**
  - `city` (required) – the city name, e.g. `Sofia`, `Burgas`, etc.

Example:

```http
GET /api/weather?city=Sofia
Accept: application/json
```

### Response (example shape)

```json
{
  "city": "Sofia",
  "current": 14.2,
  "average": 14.9,
  "trend": {
    "direction": "colder",
    "delta": -0.8,
    "label": "colder by 0.8°C"
  }
}
```

Fields:

- `city` – the city name as string
- `current` – current temperature in °C (float)
- `average` – historical average temperature in °C (float or `null` if not enough data)
- `trend`:
  - `direction` – `"hotter"`, `"colder"` or `"same"`
  - `delta` – difference in °C between current and average
  - `label` – human‑readable description

Validation:

- If `city` is missing or invalid, the controller returns **HTTP 400** with an error payload.

---

## Importing Weather Measurements (Console Command)

To import current measurements for one or more cities and store them in the database as `WeatherMeasurement` records:

```bash
php bin/console app:weather:import-measurement Sofia
```

You can pass multiple cities:

```bash
php bin/console app:weather:import-measurement Sofia Varna Burgas
```

Internally:

- The command calls `ImportCityWeatherServiceInterface`.
- The service uses:
  - `CurrentWeatherProviderInterface` to fetch live temperatures.
  - `WeatherHistoryPortInterface` to persist `WeatherMeasurement` using the Doctrine adapter.

---

## Testing

### Behat (API / BDD tests)

Configuration: `behat.yml`  
Features: `features/weather_api.feature`  
Context: `tests/Behat/ApiContext.php`

Run all Behat tests:

```bash
vendor/bin/behat
```

Current scenarios:

1. **Basic weather summary for Sofia**

   - Sends `GET /api/weather?city=Sofia`
   - Expects status `200` and that JSON contains at least the `city` field.

2. **Weather summary structure for a valid city (Burgas)**

   - Sends `GET /api/weather?city=Burgas`
   - Expects status `200`
   - Verifies:
     - `city` is a string
     - `current` is numeric
     - `average` field exists (value can be `null` or numeric)
     - `trend` exists and contains:
       - `direction` (string)
       - `delta` (numeric)
       - `label` (string)

3. **Missing city parameter should return 400**

   - Sends `GET /api/weather` with no query parameters
   - Expects status `400`.

These tests use the Symfony kernel in `test` environment against the `weather_test` database (configured via `.env.test`).

### Database for tests

The test environment uses a separate database (e.g. `weather_test`), usually configured through:

- `.env.test` with `DATABASE_URL` pointing to test DB.
- `config/packages/test/doctrine.yaml` may append `_test` suffix.

To set up test DB:

```bash
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test -n
```

Then run Behat and any other tests.

### PHPUnit

PHPUnit is available via:

- `vendor/bin/phpunit` (standard)
- or Symfony bridge (`php bin/phpunit`) if configured

To run all PHPUnit tests:

```bash
vendor/bin/phpunit
# or
php bin/phpunit
```

At the moment, the focus is on Behat for API behaviour; PHPUnit can be used to cover pure domain logic (e.g. `SimpleTrendCalculator`, `WeatherSummary`, `Trend` value object, etc.).

---

## Notes & Possible Extensions

- Add more unit tests in `tests/` for Domain services and Value Objects.
- Extend `GetCityWeatherService` to support configurable history window (e.g. last N days).
- Add caching via Redis for current weather responses.
- Add additional endpoints if needed (e.g. `/api/weather/history`).

---

## Summary

This project demonstrates:

- A clean **DDD + Hexagonal** architecture in Symfony 7
- Separation between Domain, Application (use cases, ports), and Infrastructure (adapters)
- Integration with external HTTP API (OpenWeather) and PostgreSQL via Doctrine
- Behaviour‑driven API tests with Behat and the FriendsOfBehat SymfonyExtension

It is ready to be used as a template or reference for future microservices that require clear layering, testability and explicit boundaries between domain logic and infrastructure.

