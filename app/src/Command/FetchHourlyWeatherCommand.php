<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\Weather\HourlyWeatherFetcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:weather:refresh',
    description: 'Fetches current weather for configured cities and stores measurements',
)]
class FetchHourlyWeatherCommand extends Command
{
    /**
     * @param string[] $defaultCities
     */
    public function __construct(
        private readonly HourlyWeatherFetcher $fetcher,
        private readonly array $defaultCities = [],
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'city',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Cities to refresh (override configured list)',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cities = $input->getArgument('city') ?: $this->defaultCities;

        if (empty($cities)) {
            $output->writeln('<comment>No cities configured to refresh.</comment>');

            return Command::SUCCESS;
        }

        $this->fetcher->refreshMany($cities);

        $output->writeln(sprintf(
            'Refreshed weather for cities: %s',
            implode(', ', $cities),
        ));

        return Command::SUCCESS;
    }
}
