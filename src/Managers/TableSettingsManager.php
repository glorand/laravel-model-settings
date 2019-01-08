<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Models\ModelSettings;

class TableSettingsManager extends AbstractSettingsManager
{
    public function apply(array $settings = []): SettingsManagerContract
    {
        if (!$modelSettings = $this->model->modelSettings) {
            $modelSettings = new ModelSettings();
            $modelSettings->model()->associate($this->model);
        }
        $modelSettings->settings = $settings;
        $modelSettings->save();

        return $this;
    }

    public function delete(string $path = null): SettingsManagerContract
    {
        if (!$path) {
            if ($this->model->modelSettings) {
                $this->model->modelSettings->delete();
            }
        } else {
            $settings = $this->all();
            array_forget($settings, $path);
            $this->apply($settings);
        }

        return $this;
    }

    public function set(string $path, $value): SettingsManagerContract
    {
        $settings = $this->all();
        array_set($settings, $path, $value);

        return $this->apply($settings);
    }
}
