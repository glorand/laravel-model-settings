<?php

namespace Glorand\Model\Settings\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Schema;

class CreateSettingsFieldForModel extends Command
{
    /** @var string */
    protected $signature = 'model-settings:model-settings-field';

    /** @var string */
    protected $description = 'Create migration for the (update) selected table, adding settings field';

    /**
     * @param \Illuminate\Filesystem\Filesystem $file
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle(Filesystem $file)
    {
        $table = strtolower($this->ask('What is the name of the table?'));
        $table = Str::snake(trim($table));
        if (empty($table)) {
            $this->error('The name of the table is required!');

            return false;
        }
        $fieldName = 'settings';
        if (!Schema::hasTable($table)) {
            $this->error('Unable to find table "' . $table . '" on the current DB!');

            return false;
        }
        if (Schema::hasColumn($table, $fieldName)) {
            $this->error('Field "' . $fieldName . '" already exists on table "' . $table . '"');

            return false;
        }

        $fileName = date('Y_m_d_His') . '_update_' . $table . '_table_add_' . $fieldName . '.php';
        $path = database_path('migrations') . '/' . $fileName;
        $className = 'Update' . ucfirst($table) . 'TableAddSettings';


        $stub = $file->get(__DIR__ . '/../../stubs/create_settings_field.stub');
        $stub = str_replace('DummyClass', $className, $stub);
        $stub = str_replace('DummyTable', $table, $stub);
        $stub = str_replace('settingsFieldName', $fieldName, $stub);

        $file->put($path, $stub);

        $this->line("<info>Created Migration:</info> {$fileName}");

        return true;
    }
}
