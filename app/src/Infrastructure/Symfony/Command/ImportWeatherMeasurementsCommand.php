<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Command;

use App\Application\Services\ImportCityWeatherServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:weather:import-measurement',
    description: 'Imports current weather measurements for one or more cities',
)]
class ImportWeatherMeasurementsCommand extends Command
{
    public function __construct(
        private readonly ImportCityWeatherServiceInterface $importCityWeatherService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'city',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'City or list of cities (space-separated): Sofia Varna Burgas',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string[] $cityNames */
        $cityNames = $input->getArgument('city');

        foreach ($cityNames as $cityName) {
            try {
                $measurement = $this->importCityWeatherService->importForCity($cityName);

                $output->writeln(\sprintf(
                    'Imported measurement for %s: %s at %s',
                    (string) $measurement->city(),
                    (string) $measurement->temperature(),
                    (string) $measurement->measurementTime(),
                ));
            } catch (\Throwable $e) {
                $output->writeln(\sprintf(
                    '<error>Error importing %s: %s</error>',
                    $cityName,
                    $e->getMessage(),
                ));
            }
        }

        return Command::SUCCESS;
    }
}
