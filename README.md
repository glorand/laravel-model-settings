<p align="center">
<img height="90px" alt="laravel" src="https://user-images.githubusercontent.com/883989/50478539-685da980-09da-11e9-8251-18003e023ac9.png">
</p>

<h6 align="center">
    Model Settings for your Laravel app
</h6>

<p align="center">
<a href="https://packagist.org/packages/glorand/laravel-model-settings">
 <img src="https://poser.pugx.org/glorand/laravel-model-settings/v/stable" alt="Latest Stable Version">
</a>
 <a href="https://travis-ci.com/glorand/laravel-model-settings">
 <img src="https://travis-ci.com/glorand/laravel-model-settings.svg?branch=master" alt="Build Status">
 </a>
 <a href="LICENSE.md">
 <img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat" alt="Software License">
 </a>
 <a href="https://github.styleci.io/repos/163381474">
 <img src="https://github.styleci.io/repos/163381474/shield?branch=master" alt="StyleCI">
 </a>
 <a href="https://scrutinizer-ci.com/g/glorand/laravel-model-settings/">
 <img src="https://scrutinizer-ci.com/g/glorand/laravel-model-settings/badges/quality-score.png?b=master" alt="Scrutinizer Code Quality">
 </a>
</p>

The package requires PHP 7.1.3+ and follows the FIG standards PSR-1, PSR-2 and PSR-4 
to ensure a high level of interoperability between shared PHP.

Bug reports, feature requests, and pull requests can be submitted by following our [Contribution Guide](CONTRIBUTING.md).

## Table of contents
- [Installation](#installation)
- [Updating your Eloquent Models](#update_models)
    - [Option 1 - `HasSettingsField` trait](#update_models_1)
    - [Option 2 - `HasSettingsTable` trait](#update_models_2)
- [Usage](#usage)
    - [Get all model's settings](#get_all)
    - [Get a specific setting](#get)
    - [Add / Update setting](#add_update)
    - [Check if the model has a specific setting](#check)
    - [Remove a setting from a model](#remove)
 - [Changelog](#changelog)
 - [Contributing](#contributing)
- [License](#license)

## Installation <a name="installation"></a>
```shell
$ composer require glorand/laravel-model-settings
```

```
{
    "require": {
        "glorand/laravel-model-settings": "^1.0"
    }
}
```

after run:
```
$ php artisan migrate
```

## Updating your Eloquent Models <a name="update_models"></a>
Your models should use the `HasSettingsField` or `HasSettingsTable` trait.

#### Option 1 - `HasSettingsField` trait <a name="update_models_1"></a>
You must also add `settings` to your fillable array as shown in the example below
```php
use Glorand\Model\Settings\Traits\HasSettingsField;

class User extends Model
{
    use HasSettingsField;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'settings'
    ];

}
```
#### Option 2 - `HasSettingsTable` trait <a name="update_models_2"></a>
```php
use Glorand\Model\Settings\Traits\HasSettingsTable;

class User extends Model
{
    use HasSettingsTable;
}
```

## Usage <a name="usage"></a>

#### Get all model's settings <a name="get_all"></a>
```php
$user = App\User::first();

$user->settings()->all();
$user->settings()->get();
```

#### Get a specific setting <a name="get"></a>
```php
$user = App\User::first();

$user->settings()->get('some.setting');
$user->settings()->get('some.setting', 'default value');
$user->settings('some.setting');
```

#### Add / Update setting <a name="add_update"></a>
```php
$user = App\User::first();

$user->settings()->apply((array)$settings);
$user->settings()->set('some.setting', 'new value');
$user->settings()->update('some.setting', 'new value');
```

#### Check if the model has a specific setting <a name="check"></a>
```php
$user = App\User::first();

$user->settings()->has('some.setting');
```

#### Remove a setting from a model <a name="remove"></a>
```php
$user = App\User::first();

$user->settings()->delete('some.setting');
```

## Changelog <a name="changelog"></a>
Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing <a name="contributing"></a>
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License <a name="license"></a>
The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
