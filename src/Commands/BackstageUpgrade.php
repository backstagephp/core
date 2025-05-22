<?php

namespace Backstage\Commands;

use Backstage\Events\BackstageUpgraded;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'backstage:upgrade')]
class BackstageUpgrade extends Command
{
    public $signature = 'backstage:upgrade';

    public $description = 'Upgrade backstage to the latest version';

    public function handle(): int
    {

        foreach ([
            'vendor:publish --tag=backstage-migrations',
            'migrate'
        ] as $command) {
            $this->call($command);
        }

        BackstageUpgraded::dispatch();

        $this->components->info('Successfully upgraded!');

        return static::SUCCESS;
    }
}
