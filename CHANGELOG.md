# Changelog

All notable changes to `glorand/laravel-model-settings` will be documented in this file

## 9.0.0 - 2026-07-20
### Added
- Single `HasSettings` trait with configuration-driven driver selection (`MODEL_SETTINGS_DRIVER` env / `driver` config / per-model `$settingsDriver` property)
- `SettingsManagerFactory` singleton with `extend()` for registering custom drivers at runtime
- Driver-scoped config: driver-specific keys live under `drivers.<name>.*`
- Optional Redis driver options: `drivers.redis.connection` (named connection) and `drivers.redis.key_prefix`
### Changed (BREAKING)
- Removed the `HasSettingsField`, `HasSettingsTable` and `HasSettingsRedis` traits - see [MIGRATION_GUIDE_8_to_9.md](MIGRATION_GUIDE_8_to_9.md)
- Removed the flat `settings_*` config keys (env variable names are unchanged)
- Requires PHP ^8.2 and Laravel 12/13 (drop Laravel 10/11, PHP 8.1)
- Mutations (`set`, `update`, `delete`, `setMultiple`, `deleteMultiple`) persist only the stored overrides - defaults are no longer copied into storage; validation runs against the effective (default-merged) result
- The `table` and `redis` drivers throw `ModelSettingsException` when the model has no primary key yet
- The `redis` driver deletes the storage key when settings are cleared instead of storing an empty array
### Fix
- The field driver's internal schema-check cache key now includes the table name (two models on different tables no longer share one cached result)

## 8.0.1 - 2025-03-15
### Added
- Rename HasSettings::getRules to HasSettings::getSettingsRules

## 8.0.0 - 2025-02-28
### Added
- Add support for Laravel 12

## 7.0.0 - 2024-03-13
### Added
- Add support for Laravel 11
- 8.1 min. php version

## 5.0.0 - 2022-02-10
### Added
- Add support for Laravel 9 by @belzaaron
- DROP Laravel 7,8 support
- 8.0 min. php version

## 4.4.1 - 2021-11-13
### Fix
- fix validation variable name

## 4.4.0 - 2021-11-01
### Added
- Validation system for settings data

## 4.3.0 - 2021-10-11
### Added
- Using another method name other than settings()

## 4.2.2 - 2021-04-07
### Fix param type

## 4.2.1 - 2020-12-14
### Added
- PHP8 support

## 4.2.0 - 2020-12-14
### Added
- Refactor the work with default settings (flatten arrays)

## 4.1.0 - 2020-12-03
### Added
- Refactor unit tests

## 4.0.2 - 2020-12-01
### Added
- Setup connection of the model in HasSettingsTableTrait

## 4.0.1 - 2020-11-19
### Added
- Update README

## 4.0.0 - 2020-09-11
### Added
- Add support for Laravel 8
- Drop support for Laravel 5.8 and lower
- Drop support for PHP 7.1 and lower

## 3.7.0 - 2020-09-10
### Added
- HasSettingsField now adheres to $connection override on model [Ref. task](https://github.com/glorand/laravel-model-settings/issues/62)

## 3.6.7 - 2020-08-27
### Fix
- code refactor

## 3.6.6 - 2020-08-25
### Fix
- default configs for a table in model_settings.php config file 

## 3.6.5 - 2020-05-07
### Fix
- code refactor
- use Cache facade instead of cache() helper function

## 3.6.4 - 2020-04-24
### Fix
- github actions

## 3.6.3 - 2020-04-21
### Fix
- "empty()" - to check if the model has empty setting

## 3.6.2 - 2020-04-20
### Fix
- "exist()" - to check if the model has valid setting 

## 3.6.1 - 2020-03-23
### Fix
- https://github.com/glorand/laravel-model-settings/issues/50

## 3.6.0 - 2020-03-03

- add support for Laravel 7

## 3.5.5 - 2020-02-04
### Bugfix

## 3.5.4 - 2019-12-20
### Fix
- https://github.com/glorand/laravel-model-settings/issues/40

## 3.5.3 - 2019-12-11
### Fix
- https://github.com/glorand/laravel-model-settings/issues/36

## 3.5.2 - 2019-12-05
### Fix
- https://github.com/glorand/laravel-model-settings/issues/33

## 3.5.1 - 2019-11-29
### Fix
- Check column settings exists directly on the database table

## 3.5.0 - 2019-10-14
### Added
- Use cache in case of Table Settings [Ref. task](https://github.com/glorand/laravel-model-settings/issues/25)
- Refactor

## 3.4.2 - 2019-09-25
### Added
- Manage array type settings

## 3.4.1 - 2019-09-17
### Added
- Redis support

## 3.3.0 - 2019-08-29
### Added
- Default settings on Model

## 3.2.0 - 2019-08-07
### Added
- Compatibility with PSR-16, CacheInterface

## 3.1.1 - 2019-07-01
### Added
- Test for console commands

## 3.1.0 - 2019-06-28
### Added
- Configure persistence for settings in case of Field Type (HasSettingsField)

## 3.0.0 - 2019-03-21
### Added
- Command to create the table for settings
- Dynamic name for the settings table and field

## 2.0.0 - 2019-01-26
### Added
- Console Commands

## 1.0.0 - 2019-01-08
### Added
- Initial release
