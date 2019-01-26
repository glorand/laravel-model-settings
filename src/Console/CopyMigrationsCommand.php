<?php

namespace Glorand\Model\Settings\Console;

use Illuminate\Console\Command;

class CopyMigrationsCommand extends Command
{
    /** @var string */
    protected $signature = 'model-settings:copy-migrations';

    /** @var string */
    protected $description = 'Copy migrations files from package to app\'s migration folder';

    public function handle()
    {
        \File::copyDirectory(__DIR__ . '/../../migrations', database_path('migrations'));

        $this->info('Migration files copied successfully.');
    }
}
