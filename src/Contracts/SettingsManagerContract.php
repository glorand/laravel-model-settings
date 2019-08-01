<?php

namespace Glorand\Model\Settings\Contracts;

use Illuminate\Database\Eloquent\Model;

interface SettingsManagerContract
{
    public function __construct(Model $model);

    public function all(): array;

    public function apply(array $settings = []): self;

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

    //public function setMultiple(iterable $values): self;

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
     * @param iterable $paths A list of string-based paths to be deleted.
     * @return \Glorand\Model\Settings\Contracts\SettingsManagerContract
     */
    public function deleteMultiple(iterable $paths): self;

    //public function clear(): self;
}
