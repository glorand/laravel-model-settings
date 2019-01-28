<?php

namespace Glorand\Model\Settings;

use Glorand\Model\Settings\Console\CreateSettingsFieldForModel;
use Glorand\Model\Settings\Console\CreateSettingsTable;
use Illuminate\Support\ServiceProvider;

class ModelSettingsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateSettingsFieldForModel::class,
                CreateSettingsTable::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/model_settings.php' => config_path('model_settings.php'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/model_settings.php', 'model_settings');
    }
}
