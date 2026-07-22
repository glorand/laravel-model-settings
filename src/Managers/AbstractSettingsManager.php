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
     * Decode a stored JSON string, falling back to an empty array on null or invalid JSON.
     * @param string|null $value
     * @return array
     */
    protected function decodeJsonValue(?string $value): array
    {
        $decoded = json_decode($value ?? '[]', true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return string
     */
    protected function getModelConnection(): string
    {
        return $this->model->getConnectionName() ?? config('database.default');
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
        return $this->mergeFlattenedWithDefaults(static::dotFlatten($this->getStoredValue()));
    }

    /**
     * Merge a flat dot-notation settings array over the flattened default settings.
     * @param array $flattenedSettings
     * @return array
     */
    protected function mergeFlattenedWithDefaults(array $flattenedSettings): array
    {
        if (!empty($flattenedSettings) && !static::isAssoc($flattenedSettings)) {
            // The settings are a plain sequential list at the top level. A list has no
            // per-item identity to merge against the defaults by index, so treat it as a
            // full replacement instead of letting index-keyed leaves collide with the defaults'.
            return $flattenedSettings;
        }

        $result = static::dotFlatten($this->model->getDefaultSettings());
        foreach ($flattenedSettings as $key => $value) {
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Rebuild a nested array from a flat dot-notation keyed array.
     * @param array $flat
     * @return array
     */
    protected function reconstructNestedFromFlat(array $flat): array
    {
        $nested = [];
        foreach ($flat as $key => $value) {
            Arr::set($nested, $key, $value);
        }

        return $nested;
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
        $settingsArray = $this->reconstructNestedFromFlat($this->allFlattened());
        if (is_null($paths)) {
            return $settingsArray;
        }

        $array = [];
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
        $effective = $this->reconstructNestedFromFlat(
            $this->mergeFlattenedWithDefaults(static::dotFlatten($settings))
        );

        Validator::make($effective, Arr::wrap($this->model->getSettingsRules()))->validate();
    }
}
