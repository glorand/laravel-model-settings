# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

`glorand/laravel-model-settings` is a Laravel package that adds a `settings()` accessor to any Eloquent model, exposing a fluent API for reading/writing nested settings (dot-notation paths). It is a library, not an application — there is no app to run; behavior is verified through the test suite against multiple Laravel versions via Orchestra Testbench.

## Commands

```bash
composer test                  # Run the full PHPUnit suite (vendor/bin/phpunit)
composer test:coverage:text    # Run with text coverage report
composer fix:style             # Apply PHP-CS-Fixer (PSR-2, risky rules) to src/

vendor/bin/phpunit --filter testMethodName            # Run a single test method
vendor/bin/phpunit tests/FieldSettingsManagerTest.php # Run a single test file
```

Targets PHP 8.1+ and Laravel 10–13. CI (`.github/workflows/test.yml`) runs a matrix across PHP 8.1–8.4, Laravel 10–13, and `prefer-lowest`/`prefer-stable`. Code follows PSR-1/2/4/12.

## Architecture

Three independent backends, each wired by a trait + a manager, all sharing one abstract base:

- **Storage traits** (the public entry point a model `use`s):
  - `HasSettingsField` — stores settings as a JSON column on the model's own table (default column `settings`).
  - `HasSettingsTable` — stores settings in a separate `model_settings` table via a polymorphic `MorphOne` relation (`ModelSettings` model).
  - `HasSettingsRedis` — stores settings in Redis.
  - All three `use HasSettings` (the shared base trait) and implement `settings(): SettingsManagerContract` + `getSettingsValue(): array`.

- **Managers** (`src/Managers/`) hold all the read/query logic in `AbstractSettingsManager` (`get`, `set`, `has`, `all`, `getMultiple`, `delete`, `clear`, dot-flattening, validation). Each concrete manager (`Field`, `Table`, `Redis`) only implements `apply(array $settings)` — the persistence strategy. `set/update/delete/clear/setMultiple/deleteMultiple` all funnel through `apply()`, so changing how data is saved means touching only `apply()`.

- **`HasSettings` base trait** (`src/Traits/HasSettings.php`) supplies cross-backend behavior: `getDefaultSettings()` (model property → config fallback), `getSettingsRules()`, and the `__call` override that forwards a custom method name (`$invokeSettingsBy`) to `settings()`.

### Key behaviors to preserve

- **Default settings are merged on read**, not stored. `all()`/`getMultiple()` merge `getDefaultSettings()` under the persisted value via dot-flattening (`AbstractSettingsManager::dotFlatten` + `allFlattened`). A model-level `$defaultSettings` property overrides the config `defaultSettings.<table>` entry entirely.
- **Validation** runs inside `apply()` (`validate()` → Laravel `Validator`) against the model's `$settingsRules` before persisting; rule syntax matches standard Laravel validation rules.
- **Persistence toggle** applies to `HasSettingsField` only: `isPersistSettings()` (model `$persistSettings` property / `MODEL_SETTINGS_PERSISTENT` env / config) decides whether `apply()` auto-saves the model. When false, the caller must `save()` manually.
- **Table backend caching**: `HasSettingsTable::getSettingsValue()` uses `Cache::rememberForever` keyed by `getSettingsCacheKey()` when `settings_table_use_cache` is on; `TableSettingsManager::apply()` must call `cache()->forget(...)` after writes. Preserve this invalidation when editing table writes.

### Config & artisan

- Config lives in `config/model_settings.php` (env-driven: field name, table name, persistence, cache flags, `defaultSettings`).
- `ModelSettingsServiceProvider` registers config, publishes it, and registers two console commands that publish migration stubs (`stubs/`): `model-settings:model-settings-field` (adds a JSON column) and `model-settings:model-settings-table` (creates the `model_settings` table).

## Testing notes

- Tests extend `tests/TestCase.php` (Orchestra Testbench). Each backend has its own manager test; `CommonFunctionalityTest` covers shared logic.
- Redis tests use `josiasmontag/laravel-redis-mock`.
- `tests/Models/` holds fixture models for each trait; `TestWrongModelTest` asserts the `ModelSettingsException` thrown when a manager is constructed for a model missing `HasSettings`.
