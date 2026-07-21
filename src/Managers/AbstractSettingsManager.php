<?php

namespace Glorand\Model\Settings\Managers;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Exceptions\ModelSettingsException;
use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Class AbstractSettingsManager
 * @package Glorand\Model\Settings\Managers
 *
 * Requires the model to use HasSettings trait and provide these methods:
 * @method array getDefaultSettings()
 * @method array getSettingsRules()
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
abstract class AbstractSettingsManager implements SettingsManagerContract
{
    /**
     * @var Model&HasSettings Model instance must use HasSettings trait
     */
    protected Model $model;

    protected array $defaultSettings = [];

    /**
     * @param Model $model
     * @throws ModelSettingsException
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        if (!in_array(HasSettings::class, class_uses_recursive($this->model))) {
            throw new ModelSettingsException('Wrong model, missing HasSettings trait.');
        }
    }

    /**
     * Raw persisted settings for this model (defaults NOT merged).
     *
     * @return array
     */
    abstract public function getStoredValue(): array;

    /**
     * @throws ModelSettingsException
     */
    protected function ensureModelIsPersisted(): void
    {
        if (null === $this->model->getKey()) {
            throw new ModelSettingsException(
                sprintf('Model [%s] must be saved before accessing its settings.', get_class($this->model))
            );
        }
    }

    /**
     * Check if array is associative and not sequential
     * @param array $arr
     * @return bool
     */
    private static function isAssoc(array $arr): bool
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Flatten array with dots for settings package
     * @param array $array
     * @param string $prepend
     * @return array
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public static function dotFlatten(array $array, string $prepend = ''): array
    {
        $results = [];
        foreach ($array as $key => $value) {
            // only re-run if nested array is associative (key-based)
            if (is_array($value) && static::isAssoc($value) && !empty($value)) {
                $results = array_merge($results, static::dotFlatten($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
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
        return $this->getMultiple();
    }

    /**
     * Get flat merged array with dot-notation keys
     * @return array
     */
    public function allFlattened(): array
    {
        $flattenedDefaultSettings = static::dotFlatten($this->model->getDefaultSettings());
        $flattenedSettingsValue = static::dotFlatten($this->getStoredValue());

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
     * @param mixed $default
     * @return mixed
     */
    public function get(?string $path = null, mixed $default = null): mixed
    {
        return $path ? Arr::get($this->all(), $path, $default) : $this->all();
    }

    /**
     * @param iterable|null $paths
     * @param null $default
     * @return array
     */
    public function getMultiple(?iterable $paths = null, $default = null): array
    {
        $array = [];
        $allFlattened = $this->allFlattened();
        $settingsArray = [];
        foreach ($allFlattened as $key => $value) {
            Arr::set($settingsArray, $key, $value);
        }
        if (is_null($paths)) {
            return $settingsArray;
        }

        foreach ($paths as $path) {
            Arr::set($array, $path, Arr::get($settingsArray, $path, $default));
        }

        return $array;
    }

    /**
     * @param string $path
     * @param mixed $value
     * @return SettingsManagerContract
     */
    public function set(string $path, mixed $value): SettingsManagerContract
    {
        $settings = $this->getStoredValue();
        Arr::set($settings, $path, $value);

        return $this->apply($settings);
    }

    /**
     * @param string $path
     * @param mixed $value
     * @return SettingsManagerContract
     */
    public function update(string $path, mixed $value): SettingsManagerContract
    {
        return $this->set($path, $value);
    }

    /**
     * @param string|null $path
     * @return SettingsManagerContract
     */
    public function delete(?string $path = null): SettingsManagerContract
    {
        if (!$path) {
            $settings = [];
        } else {
            $settings = $this->getStoredValue();
            Arr::forget($settings, $path);
        }

        $this->apply($settings);

        return $this;
    }

    public function clear(): SettingsManagerContract
    {
        return $this->delete();
    }

    /**
     * @param iterable $values
     * @return SettingsManagerContract
     */
    public function setMultiple(iterable $values): SettingsManagerContract
    {
        $settings = $this->getStoredValue();
        foreach ($values as $path => $value) {
            Arr::set($settings, $path, $value);
        }

        return $this->apply($settings);
    }

    /**
     * @param iterable $paths
     * @return SettingsManagerContract
     */
    public function deleteMultiple(iterable $paths): SettingsManagerContract
    {
        $settings = $this->getStoredValue();
        foreach ($paths as $path) {
            Arr::forget($settings, $path);
        }

        $this->apply($settings);

        return $this;
    }

    /**
     * Validates the candidate effective result (defaults merged with the
     * proposed settings), while only the proposed settings get persisted.
     *
     * @param array $settings
     * @throws ValidationException
     */
    protected function validate(array $settings): void
    {
        $flattened = array_merge(
            static::dotFlatten($this->model->getDefaultSettings()),
            static::dotFlatten($settings)
        );
        $effective = [];
        foreach ($flattened as $key => $value) {
            Arr::set($effective, $key, $value);
        }

        Validator::make($effective, Arr::wrap($this->model->getSettingsRules()))->validate();
    }
}
