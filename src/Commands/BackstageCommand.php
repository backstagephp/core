<?php

namespace Vormkracht10\Backstage\Commands;

use Illuminate\Console\Command;

class BackstageCommand extends Command
{
    public $signature = 'backstage';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
