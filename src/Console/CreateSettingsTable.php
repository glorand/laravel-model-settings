<?php

namespace Glorand\Model\Settings\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreateSettingsTable extends Command
{
    /** @var string */
    protected $signature = 'model-settings:model-settings-table';

    /** @var string */
    protected $description = 'Create migration for the settings table';

    /**
     * @param \Illuminate\Filesystem\Filesystem $file
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle(Filesystem $file)
    {
        $table = strtolower(config('model_settings.settings_table_name'));
        $table = Str::snake(trim($table));
        if (empty($table)) {
            $this->error('The name of the table is required!');

            return 1;
        }

        if (Schema::hasTable($table)) {
            $this->error('Table "' . $table . '" already exists!');

            return 2;
        }

        $fileName = date('Y_m_d_His') . '_create_' . $table . '_table.php';
        $path = database_path('migrations') . '/' . $fileName;
        $className = 'Create' . ucfirst(Str::camel($table)) . 'Table';


        $stub = $file->get(__DIR__ . '/../../stubs/create_settings_table.stub');
        $stub = str_replace('DummyClass', $className, $stub);
        $stub = str_replace('DummyTable', $table, $stub);

        $file->replace($path, $stub);
        $this->line("<info>Created Migration:</info> {$fileName}");

        return 0;
    }
}
