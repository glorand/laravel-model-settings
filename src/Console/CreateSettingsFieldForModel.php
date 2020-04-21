<?php

namespace Glorand\Model\Settings\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
        $table = $this->ask('What is the name of the table?');
        $table = strtolower(Str::snake(trim($table)));
        if (empty($table)) {
            $this->error('The name of the table is required!');

            return 1;
        }
        if (!Schema::hasTable($table)) {
            $this->error('Unable to find table "' . $table . '" on the current DB!');

            return 2;
        }

        $defaultFieldName = config('model_settings.settings_field_name');
        $fieldName = $this->ask(
            'What is the name of the settings field name?',
            $defaultFieldName
        );
        $fieldName = strtolower(Str::snake(trim($fieldName)));

        if (Schema::hasColumn($table, $fieldName)) {
            $this->error('Field "' . $fieldName . '" already exists on table "' . $table . '"');

            return 3;
        }

        $fileName = date('Y_m_d_His') . '_update_' . $table . '_table_add_' . $fieldName . '.php';
        $path = database_path('migrations') . '/' . $fileName;
        $className = 'Update' . ucfirst($table) . 'TableAdd' . ucfirst(Str::camel($fieldName));


        $stub = $file->get(__DIR__ . '/../../stubs/create_settings_field.stub');
        $stub = str_replace('DummyClass', $className, $stub);
        $stub = str_replace('DummyTable', $table, $stub);
        $stub = str_replace('settingsFieldName', $fieldName, $stub);

        $file->replace($path, $stub);

        $this->line("<info>Created Migration:</info> {$fileName}");

        return 0;
    }
}
