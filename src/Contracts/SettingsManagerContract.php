<?php

namespace Glorand\Model\Settings\Contracts;

use Illuminate\Database\Eloquent\Model;

interface SettingsManagerContract
{
    public function __construct(Model $model);

    public function all(): array;

    public function empty(): bool;

    public function exist(): bool;

    public function apply(array $settings = []): self;

    /**
     * Fetches a value from the settings.
     *
     * @param string|null $path if null returns all the settings array
     * @param null $default default value to return if the path does not exist
     * @return mixed
     */
    public function get(string $path = null, $default = null);

    /**
     * Obtains multiple items by their paths.
     *
     * @param iterable $paths
     * @param null $default
     * @return iterable A list of key => value pairs.
     * Paths that do not exist will have $default as value.
     */
    public function getMultiple(iterable $paths, $default = null): iterable;

    public function has(string $path): bool;

    public function set(string $path, $value): self;

    /**
     * Persists a set of key => value pairs in settings.
     *
     * @param iterable $values
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function setMultiple(iterable $values): self;

    public function update(string $path, $value): self;

    /**
     * Delete an item by its unique path.
     *
     * @param string|null $path
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function delete(string $path = null): self;

    /**
     * Deletes multiple setting items in a single operation.
     *
     * @param iterable $paths a list of string-based paths to be deleted
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function deleteMultiple(iterable $paths): self;

    /**
     * Wipes clean the entire settings for the model.
     *
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function clear(): self;
}
