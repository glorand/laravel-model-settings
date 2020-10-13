<?php

namespace Glorand\Model\Settings\Managers;

use Exception;
use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

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
     * Check if array is associative and not sequential
     * @param array $arr
     * @return bool
     */
    public static function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Flatten array with dots for settings package
     * @param $array
     * @param string $prepend
     * @return array
     */
    public static function dotFlatten($array, $prepend = '') {
        $results = [];
        foreach ($array as $key => $value) {
            // only re-run if nested array is associative (key-based)
            // cannot use pre-shipped Arr:dot method
            if (is_array($value) && static::isAssoc($value) && !empty($value)) {
                $results = array_merge($results, static::dotFlatten($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }
        return $results;
    }

    /**
     * Get nested merged array with all available keys
     * @return array
     */
    public function all(): array
    {
        if (config('model_settings.settings_cache_all') && !($this instanceof RedisSettingsManager)) {
            return Cache::remember($this->getAllSettingsCacheKey(), now()->addDay(), function () {
                return $this->getMultiple(null);
            });
        }
        return $this->getMultiple(null);
    }

    /**
     * Get flat merged array with dot-notation keys
     * @return array
     */
    public function allFlattened()
    {
        $flattenedDefaultSettings = static::dotFlatten($this->model->getDefaultSettings());
        $flattenedSettingsValue = static::dotFlatten($this->model->getSettingsValue());
        return array_merge($flattenedDefaultSettings, $flattenedSettingsValue);
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
     * @return array
     */
    public function getMultiple(iterable $paths = null, $default = null): array
    {
        $array = [];
        $allFlattened = $this->allFlattened();
        if (is_null($paths)) {
            $paths = array_keys($allFlattened);
        }

        foreach ($paths as $path) {
        	// Get default value
	        $defaultValue = null;
	        if(is_array($default)){
		        $defaultValue = Arr::get($default, $path);
	        }else{
	        	$defaultValue = $default;
	        }
            Arr::set($array, $path, Arr::get($allFlattened, $path, $defaultValue));
        }

        return $array;
    }

    /**
     * @param string $path
     * @param $value
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function set(string $path, $value): SettingsManagerContract
    {
        $settings = $this->model->getSettingsValue();
        $default = $this->model->getDefaultSettings();
        if ($value === Arr::get($default, $path)) {
            Arr::forget($settings, $path);
            $this->forgetEmpty($settings, $path);
        } else {
            Arr::set($settings, $path, $value);
        }

        return $this->apply($settings);
    }

	/**
	 *
	 */
    protected function forgetEmpty(&$settings, $path) {
    	// Forget path if it is empty
	    if(!Arr::get($settings, $path)){
		    Arr::forget($settings, $path);
	    }else{
	    	return;
	    }

    	// Get path as array
    	$paths = explode('.', $path);

    	// remove last path
	    array_pop($paths);

	    // Return if it was the last
	    if(count($paths) === 0){
	    	return;
	    }

	    $this->forgetEmpty($settings, implode('.', $paths));
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
            $settings = $this->model->getSettingsValue();
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
        $settings = $this->model->getSettingsValue();
        $default = $this->model->getDefaultSettings();
        $flattenedValues = static::dotFlatten($values);
        foreach ($flattenedValues as $path => $value) {
            if ($value === Arr::get($default, $path)) {
                Arr::forget($settings, $path);
            } else {
                Arr::set($settings, $path, $value);
            }
        }

        return $this->apply($settings);
    }

    /**
     * @param iterable $paths
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function deleteMultiple(iterable $paths): SettingsManagerContract
    {
        $settings = $this->model->getSettingsValue();
        foreach ($paths as $path) {
            Arr::forget($settings, $path);
        }

        $this->apply($settings);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function forgetAllSettings()
    {
        cache()->forget($this->getAllSettingsCacheKey());
    }

    /**
     * @return string
     */
    public function getAllSettingsCacheKey(): string
    {
        return config('model_settings.settings_table_cache_prefix') . $this->model->getTable() . ':' . $this->model->getKey() . '::all';
    }
}
