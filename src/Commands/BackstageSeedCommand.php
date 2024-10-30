<?php

namespace Vormkracht10\Backstage\Commands;

use Illuminate\Console\Command;

class BackstageSeedCommand extends Command
{
    public $signature = 'backstage:seed';

    public $description = 'Seed database with sample Backstage data';

    public function handle(): int
    {
        if ($this->confirm('Do you really want to seed the database with sample Backstage data?')) {
            $this->call('db:seed', ['--class' => 'Vormkracht10\Backstage\Database\Seeders\BackstageSeeder']);
        }

        return self::SUCCESS;
    }
}
