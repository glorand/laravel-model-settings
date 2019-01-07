<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;

class FieldSettingsManager extends AbstractSettingsManager
{
    public function apply(array $settings = []): SettingsManagerContract
    {
        $this->model->settings = $settings;
        $this->model->save();

        return $this;
    }

    public function delete(string $path = null): SettingsManagerContract
    {
        if (!$path) {
            $settings = [];
        } else {
            $settings = $this->all();
            array_forget($settings, $path);
        }

        $this->apply($settings);

        return $this;
    }

    public function set(string $path, $value): SettingsManagerContract
    {
        $settings = $this->all();
        array_set($settings, $path, $value);

        return $this->apply($settings);
    }
}
