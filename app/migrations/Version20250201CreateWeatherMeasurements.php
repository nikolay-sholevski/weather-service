<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250201CreateWeatherMeasurements extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create weather_measurements table for storing historical weather data.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
CREATE TABLE weather_measurements (
    id SERIAL PRIMARY KEY,
    city_name VARCHAR(100) NOT NULL,
    temperature_celsius DOUBLE PRECISION NOT NULL,
    measured_at TIMESTAMP WITHOUT TIME ZONE NOT NULL
)
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS weather_measurements');
    }
}
