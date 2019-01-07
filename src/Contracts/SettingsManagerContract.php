<?php

namespace Glorand\Model\Settings\Contracts;

use Illuminate\Database\Eloquent\Model;

interface SettingsManagerContract
{
    public function __construct(Model $model);

    public function all(): array;

    public function apply(array $settings = []): self;

    public function delete(string $path = null): self;

    public function get(string $path = null, $default = null);

    public function has(string $path): bool;

    public function set(string $path, $value): self;

    public function update(string $path, $value): self;
}