<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Illuminate\Support\Facades\Redis;

/**
 * Class FieldSettingsManager
 * @package Glorand\Model\Settings\Managers
 * @property \Illuminate\Database\Eloquent\Model|\Glorand\Model\Settings\Traits\HasSettingsRedis $model
 */
class RedisSettingsManager extends AbstractSettingsManager
{
    public function apply(array $settings = []): SettingsManagerContract
    {
        Redis::set($this->model->cacheKey(), json_encode($settings));

        return $this;
    }
}
