<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250201SeedInitialWeatherData extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed sample weather measurements for Sofia for last few days.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
INSERT INTO weather_measurements (city_name, temperature_celsius, measured_at) VALUES
('Sofia', 10.5, NOW() - INTERVAL '1 day'),
('Sofia', 12.2, NOW() - INTERVAL '2 days'),
('Sofia', 8.7, NOW() - INTERVAL '3 days'),
('Sofia', 7.1, NOW() - INTERVAL '4 days'),
('Sofia', 11.3, NOW() - INTERVAL '5 days');
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM weather_measurements WHERE city_name = 'Sofia'");
    }
}
