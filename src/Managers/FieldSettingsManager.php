<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;

/**
 * Class FieldSettingsManager
 * @package Glorand\Model\Settings\Managers
 * @property \Illuminate\Database\Eloquent\Model|\Glorand\Model\Settings\Traits\HasSettingsField $model
 */
class FieldSettingsManager extends AbstractSettingsManager
{
    /**
     * @param array $settings
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function apply(array $settings = []): SettingsManagerContract
    {
        $this->model->{$this->model->getSettingsFieldName()} = json_encode($settings);
        if ($this->model->isPersistSettings()) {
            $this->model->save();
        }

        return $this;
    }
}
