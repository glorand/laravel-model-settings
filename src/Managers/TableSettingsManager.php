<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Models\ModelSettings;

/**
 * Class TableSettingsManager
 * @package Glorand\Model\Settings\Managers
 * @property  \Illuminate\Database\Eloquent\Model|\Glorand\Model\Settings\Traits\HasSettingsTable $model
 */
class TableSettingsManager extends AbstractSettingsManager
{
    /**
     * @param array $settings
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function apply(array $settings = []): SettingsManagerContract
    {
        if (!$modelSettings = $this->model->modelSettings()->first()) {
            $modelSettings = new ModelSettings();
            $modelSettings->model()->associate($this->model);
        }
        $modelSettings->settings = $settings;
        $modelSettings->save();

        return $this;
    }

    /**
     * @param string|null $path
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     * @throws \Exception
     */
    public function delete(string $path = null): SettingsManagerContract
    {
        if (!$path) {
            /** @var ModelSettings $modelSettings */
            if ($modelSettings = $this->model->modelSettings()->first()) {
                $modelSettings->delete();
            }
        } else {
            $settings = $this->all();
            array_forget($settings, $path);
            $this->apply($settings);
        }

        return $this;
    }

    /**
     * @param string $path
     * @param mixed $value
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function set(string $path, $value): SettingsManagerContract
    {
        $settings = $this->all();
        array_set($settings, $path, $value);

        return $this->apply($settings);
    }
}
