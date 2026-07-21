<?php

namespace Glorand\Model\Settings\Contracts;

use Illuminate\Database\Eloquent\Model;

interface SettingsManagerContract
{
    public function __construct(Model $model);

    public function all(): array;

    /**
     * Raw persisted settings for this model (defaults NOT merged).
     *
     * @return array
     */
    public function getStoredValue(): array;

    public function empty(): bool;

    public function exist(): bool;

    public function apply(array $settings = []): self;

    /**
     * Fetches a value from the settings.
     *
     * @param string|null $path if null returns all the settings array
     * @param mixed $default default value to return if the path does not exist
     * @return mixed
     */
    public function get(?string $path = null, mixed $default = null): mixed;

    /**
     * Obtains multiple items by their paths.
     *
     * @param iterable $paths
     * @param mixed $default
     * @return iterable A list of key => value pairs.
     * Paths that do not exist will have $default as value.
     */
    public function getMultiple(iterable $paths, mixed $default = null): iterable;

    public function has(string $path): bool;

    public function set(string $path, mixed $value): self;

    /**
     * Persists a set of key => value pairs in settings.
     *
     * @param iterable $values
     */
    public function setMultiple(iterable $values): self;

    public function update(string $path, mixed $value): self;

    /**
     * Delete an item by its unique path.
     *
     * @param string|null $path
     */
    public function delete(?string $path = null): self;

    /**
     * Deletes multiple setting items in a single operation.
     *
     * @param iterable $paths a list of string-based paths to be deleted
     */
    public function deleteMultiple(iterable $paths): self;

    /**
     * Wipes clean the entire settings for the model.
     */
    public function clear(): self;
}
