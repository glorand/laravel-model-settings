<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractSettingsManager
 * @package Glorand\Model\Settings\Managers
 */
abstract class AbstractSettingsManager implements SettingsManagerContract
{
    /** @var \Illuminate\Database\Eloquent\Model */
    protected $model;

    /**
     * AbstractSettingsManager constructor.
     * @param \Illuminate\Database\Eloquent\Model $model
     * @throws \Glorand\Model\Settings\Exceptions\ModelSettingsException
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        if (!in_array(HasSettings::class, class_uses_recursive($this->model))) {
            throw new ModelSettingsException('Wrong model, missing HasSettings trait.');
        }
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return $this->model->getSettingsValue();
    }

    /**
     * @param string $path
     * @return bool
     */
    public function has(string $path): bool
    {
        return array_has($this->all(), $path);
    }

    /**
     * @param string|null $path
     * @param null $default
     * @return array|mixed
     */
    public function get(string $path = null, $default = null)
    {
        return $path ? array_get($this->all(), $path, $default) : $this->all();
    }

    /**
     * @param string $path
     * @param mixed $value
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function update(string $path, $value): SettingsManagerContract
    {
        return $this->set($path, $value);
    }
}
