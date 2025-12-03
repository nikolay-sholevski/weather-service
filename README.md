# Weather Service – Hexagonal Symfony 7 Microservice

## Overview

This project is a small **Symfony 7** microservice that exposes a HTTP endpoint:

- `GET /api/weather?city=NAME`

It returns a **weather summary** for a city, including:

- current temperature
- historical average temperature
- a trend (getting hotter/colder and by how much)

The code is organised using **Domain-Driven Design (DDD)** and **Hexagonal Architecture** with clear separation between:

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

- **Inbound (driving) side** – how the outside world calls the application:
  - HTTP request → `GetCityWeatherController`
  - CLI command → `ImportWeatherMeasurementsCommand`

- **Outbound (driven) side** – how the application depends on external systems:
  - `CurrentWeatherProviderInterface` → implemented by:
    - `OpenWeatherCurrentWeatherProvider` (real HTTP client)
    - `FakeCurrentWeatherProvider` (for dev/testing)
  - `WeatherHistoryPortInterface` → implemented by:
    - `WeatherHistoryDoctrineAdapter` (PostgreSQL via Doctrine)

---

## Running the project

You can run the project either **natively** (PHP + local Postgres) or using **Docker Compose**.

### 1. Using Docker Compose (recommended for this setup)

If the repository contains a `docker-compose.yml` with the PHP/Symfony app, PostgreSQL and Redis services (as in this task), you can start everything with:

```bash
docker-compose up -d
```

This will:

- start the application container
- start the PostgreSQL database (and Redis, if configured)
- expose the Symfony app on **http://localhost:8080**

Once it is up, you can test the API via:

```bash
curl "http://localhost:8080/api/weather?city=Sofia"
```

> If your Docker service names differ, adjust the `docker-compose` commands accordingly (e.g. `docker compose` instead of `docker-compose`).

#### Running console commands inside Docker

Most Symfony commands should be executed inside the PHP/app container.  
For example, if your PHP service is called `app`:

```bash
docker-compose exec app php bin/console list
```

Adapt the service name (`app`, `php`, `php-fpm`, etc.) to match your `docker-compose.yml`.

### 2. Running locally (without Docker)

If you prefer to run locally:

1. Ensure you have PHP, Composer and PostgreSQL installed.
2. Configure `DATABASE_URL` in `.env` and `.env.local`.
3. Install dependencies:

   ```bash
   composer install
   ```

4. Create DB and run migrations (see below).
5. Run a web server pointing to your front controller or use Symfony CLI:

   ```bash
   symfony server:start
   ```

Then the app will be available on a port such as `https://127.0.0.1:8000` depending on how the server is configured.

---

## Database & Migrations

### Dev / Normal usage

1. **Create the database**:

   ```bash
   php bin/console doctrine:database:create
   ```

2. **Run migrations**:

   ```bash
   php bin/console doctrine:migrations:migrate
   ```

If you are using Docker, run the same commands **inside the PHP container**, for example:

```bash
docker-compose exec app php bin/console doctrine:database:create
docker-compose exec app php bin/console doctrine:migrations:migrate
```

Existing migrations (examples):

- `Version20250201CreateWeatherMeasurements`  
  – creates the `weather_measurements` table.
- `Version20250201SeedInitialWeatherData`  
  – seeds initial measurements for some cities so that trend calculations have history.

### Test environment (Behat / PHPUnit)

The test environment uses its own database (e.g. with `_test` suffix), configured via `.env.test` and `config/packages/test/doctrine.yaml`.

To initialise the **test database**:

1. **Create the test database**:

   ```bash
   php bin/console doctrine:database:create --env=test
   ```

2. **Run migrations in the test environment**:

   ```bash
   php bin/console doctrine:migrations:migrate --env=test -n
   ```

With Docker:

```bash
docker-compose exec app php bin/console doctrine:database:create --env=test
docker-compose exec app php bin/console doctrine:migrations:migrate --env=test -n
```

After this, Behat and PHPUnit will run against the migrated test DB.

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
  - `label` – human-readable description

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

With Docker:

```bash
docker-compose exec app php bin/console app:weather:import-measurement Sofia Varna Burgas
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

With Docker:

```bash
docker-compose exec app vendor/bin/behat
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

With Docker:

```bash
docker-compose exec app vendor/bin/phpunit
# or
docker-compose exec app php bin/phpunit
```

At the moment, the focus is on Behat for API behaviour; PHPUnit can be used to cover pure domain logic (e.g. `SimpleTrendCalculator`, `WeatherSummary`, `Trend` value object, etc.).

---

## Summary

This project demonstrates:

- A clean **DDD + Hexagonal** architecture in Symfony 7
- Separation between Domain, Application (use cases, ports), and Infrastructure (adapters)
- Integration with external HTTP API (OpenWeather) and PostgreSQL via Doctrine
- Behaviour-driven API tests with Behat and the FriendsOfBehat SymfonyExtension

It is ready to be used as a template or reference for future microservices that require clear layering, testability and explicit boundaries between domain logic and infrastructure.

