<?php
namespace App\Command;

use App\Service\CityAdsSyncService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:sync-cityads')]
class SyncCityAdsCommand extends Command
{
    private CityAdsSyncService $syncService;

    public function __construct(CityAdsSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Starting sync from CityAds...');
        $this->syncService->syncOffers();
        $output->writeln('Sync completed!');

        return Command::SUCCESS;
    }
}
