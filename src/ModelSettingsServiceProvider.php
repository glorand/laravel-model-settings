<?php

namespace Glorand\Model\Settings;

use Glorand\Model\Settings\Console\CopyMigrationsCommand;
use Glorand\Model\Settings\Console\CreateSettingsFieldForModel;
use Illuminate\Support\ServiceProvider;

class ModelSettingsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CopyMigrationsCommand::class,
                CreateSettingsFieldForModel::class,
            ]);
        }
    }
}
