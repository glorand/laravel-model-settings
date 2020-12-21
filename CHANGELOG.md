# Changelog

All notable changes to `glorand/laravel-model-settings` will be documented in this file

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
