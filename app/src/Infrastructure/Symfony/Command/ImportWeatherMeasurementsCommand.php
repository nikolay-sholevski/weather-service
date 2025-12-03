<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Command;

use App\Application\Ports\CurrentWeatherProviderInterface;
use App\Application\Ports\WeatherHistoryPortInterface;
use App\Domain\Entities\WeatherMeasurement;
use App\Domain\ValueObjects\City;
use App\Domain\ValueObjects\MeasurementTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:weather:import-measurement',
    description: 'Imports current weather measurements for one or more cities',
)]
#[AsPeriodicTask('5 minutes', schedule: 'default')]
class ImportWeatherMeasurementsCommand extends Command
{
    public function __construct(
        private readonly CurrentWeatherProviderInterface $currentWeatherProvider,
        private readonly WeatherHistoryPortInterface $weatherHistoryPort,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'city',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'City or list of cities (space-separated): Sofia Varna Burgas'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string[] $cityNames */
        $cityNames = $input->getArgument('city');

        if (!\is_array($cityNames) || $cityNames === []) {
            $output->writeln('<error>No cities provided</error>');

            return Command::INVALID;
        }

        foreach ($cityNames as $cityName) {
            try {
                $city = new City($cityName);

                $temperature = $this->currentWeatherProvider->getCurrentTemperature($city);

                $measurementTime = new MeasurementTime(new \DateTimeImmutable('now'));

                $measurement = new WeatherMeasurement(
                    null,                // id, DB can generate or you ignore it
                    $city,
                    $temperature,
                    $measurementTime
                );

                // 🔥 This replaces EntityManager->persist/flush
                $this->weatherHistoryPort->saveMeasurement($measurement);

                $output->writeln(\sprintf(
                    'Imported measurement for %s: %s at %s',
                    (string) $city,
                    (string) $temperature,
                    (string) $measurementTime
                ));
            } catch (\Throwable $e) {
                $output->writeln(\sprintf(
                    '<error>Error importing %s: %s</error>',
                    $cityName,
                    $e->getMessage()
                ));
            }
        }

        return Command::SUCCESS;
    }
}

