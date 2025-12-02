<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Command;

use App\Application\Ports\CurrentWeatherProviderInterface;
use App\Domain\Entities\WeatherMeasurement;
use App\Domain\ValueObjects\City;
use App\Domain\ValueObjects\MeasurementTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:weather:import-measurement',
    description: 'Imports a single current weather measurement for a given city into the database.'
)]
final class ImportWeatherMeasurementsCommand extends Command
{
    public function __construct(
        private readonly CurrentWeatherProviderInterface $currentWeatherProvider,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('city', InputArgument::REQUIRED, 'City name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cityName = (string) $input->getArgument('city');
        $city = new City($cityName);

        $temperature = $this->currentWeatherProvider->getCurrentTemperature($city);
        $measurementTime = new MeasurementTime(new \DateTimeImmutable('now'));

        $measurement = new WeatherMeasurement(
            null, // id handled by DB
            $city,
            $temperature,
            $measurementTime
        );

        $this->entityManager->persist($measurement);
        $this->entityManager->flush();

        $output->writeln(\sprintf(
            'Imported measurement for %s: %s at %s',
            (string) $city,
            (string) $temperature,
            (string) $measurementTime
        ));

        return Command::SUCCESS;
    }
}

