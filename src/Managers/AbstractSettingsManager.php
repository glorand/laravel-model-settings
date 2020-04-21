<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Class AbstractSettingsManager
 * @package Glorand\Model\Settings\Managers
 */
abstract class AbstractSettingsManager implements SettingsManagerContract
{
    /** @var \Illuminate\Database\Eloquent\Model */
    protected $model;

    /** @var array */
    protected $defaultSettings = [];

    /**
     * AbstractSettingsManager constructor.
     * @param \Illuminate\Database\Eloquent\Model|HasSettings $model
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
        $array = [];
        foreach (array_merge($this->model->getDefaultSettings(), $this->model->getSettingsValue()) as $key => $value) {
            Arr::set($array, $key, $value);
        }

        return $array;
    }

    /**
     * @return bool
     */
    public function exist(): bool
    {
        return count($this->all()) > 0;
    }

    /**
     * @return bool
     */
    public function empty(): bool
    {
        return count($this->all()) <= 0;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function has(string $path): bool
    {
        return Arr::has($this->all(), $path);
    }

    /**
     * @param string|null $path
     * @param null $default
     * @return array|mixed
     */
    public function get(string $path = null, $default = null)
    {
        return $path ? Arr::get($this->all(), $path, $default) : $this->all();
    }

    /**
     * @param iterable|null $paths
     * @param null $default
     * @return iterable
     */
    public function getMultiple(iterable $paths = null, $default = null): iterable
    {
        $values = [];
        foreach ($paths as $path) {
            $values[$path] = $this->get($path, $default);
        }

        return $values;
    }

    /**
     * @param string $path
     * @param $value
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function set(string $path, $value): SettingsManagerContract
    {
        $settings = $this->all();
        Arr::set($settings, $path, $value);

        return $this->apply($settings);
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

    /**
     * @param string|null $path
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function delete(string $path = null): SettingsManagerContract
    {
        if (!$path) {
            $settings = [];
        } else {
            $settings = $this->all();
            Arr::forget($settings, $path);
        }

        $this->apply($settings);

        return $this;
    }

    /**
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function clear(): SettingsManagerContract
    {
        return $this->delete();
    }

    /**
     * @param iterable $values
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function setMultiple(iterable $values): SettingsManagerContract
    {
        $settings = $this->all();
        foreach ($values as $path => $value) {
            Arr::set($settings, $path, $value);
        }

        return $this->apply($settings);
    }

    /**
     * @param iterable $paths
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function deleteMultiple(iterable $paths): SettingsManagerContract
    {
        $settings = $this->all();
        foreach ($paths as $path) {
            Arr::forget($settings, $path);
        }

        $this->apply($settings);

        return $this;
    }
}
