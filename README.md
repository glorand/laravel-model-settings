<p align="center">
<img height="100px" alt="laravel" src="https://user-images.githubusercontent.com/883989/60343130-f5021800-99bb-11e9-8a03-fe11746a86c2.png">
</p>

<h6 align="center">
    Model Settings for your Laravel app
</h6>

<p align="center">
<a href="https://packagist.org/packages/glorand/laravel-model-settings/stats">
<img src="https://img.shields.io/packagist/dt/glorand/laravel-model-settings?style=for-the-badge&color=red" alt="Total Downloads"/>
</a>
<br />
<a href="https://packagist.org/packages/glorand/laravel-model-settings">
 <img src="https://img.shields.io/packagist/v/glorand/laravel-model-settings" alt="Latest Stable Version">
</a>
<a href="https://github.com/glorand/laravel-model-settings/actions">
<img src="https://github.com/glorand/laravel-model-settings/workflows/Test/badge.svg" alt="'Github Actions" />
</a>
 <a href="LICENSE">
 <img src="https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat" alt="Software License">
 </a>
 <br />
 <a title="MadeWithLaravel.com Shield" href="https://madewithlaravel.com/p/laravel-model-settings/shield-link"> <img src="https://madewithlaravel.com/storage/repo-shields/1716-shield.svg" alt="Sheald"/></a>
<a title="PHP Version" href="#"><img src="https://img.shields.io/packagist/php-v/glorand/laravel-model-settings" alt="PHP Version" /></a>
</p>

The package requires PHP 8.1+ and follows the FIG standards PSR-1, PSR-2, PSR-4 and PSR-12
to ensure a high level of interoperability between shared PHP.

Bug reports, feature requests, and pull requests can be submitted by following our [Contribution Guide](CONTRIBUTING.md).

## Table of contents
- [Installation](#installation)
- [Updating your Eloquent Models](#update_models)
    - [Option 1 - `HasSettingsField` trait](#update_models_1)
    - [Option 2 - `HasSettingsTable` trait](#update_models_2)
    - [Option 3 - `HasSettingsRedis` trait](#update_models_3)
- [Default Settings](#default_settings)
- [Usage](#usage)
    - [Check if the settings for the entity is empty (exist)](#empty)
    - [Check settings (exist)](#exist)
    - [Get all model's settings](#get_all)
    - [Get a specific setting](#get)
    - [Add / Update setting](#add_update)
    - [Check if the model has a specific setting](#check)
    - [Remove a setting from a model](#remove)
    - [Persistence](#persistence)
    - [Using another method name other than `settings()`](#invokeSettingsBy)
    - [Validation system for settings data](#validation)
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
        "glorand/laravel-model-settings": "^4.0"
    }
}
```

## Env (config) variables **(.env file)**

Default name for the settings field - when you use the `HasSettingsField`

`MODEL_SETTINGS_FIELD_NAME=settings`

Default name for the settings table - when you use the `HasSettingsTable`

`MODEL_SETTINGS_TABLE_NAME=model_settings`

## Updating your Eloquent Models <a name="update_models"></a>
Your models should use the `HasSettingsField` or `HasSettingsTable` trait.

#### Option 1 - `HasSettingsField` trait <a name="update_models_1"></a>
Run the `php artisan model-settings:model-settings-field` in order to create a migration file for a table.\
This command will create a json field (default name `settings`, from config) for the mentioned table.

You can choose another than default, in this case you have to specify it in you model.
```php
public $settingsFieldName = 'user_settings';
```

Complete example:
```php
use Glorand\Model\Settings\Traits\HasSettingsField;

class User extends Model
{
    use HasSettingsField;

    //define only if you select a different name from the default
    public $settingsFieldName = 'user_settings';

    //define only if the model overrides the default connection
    protected $connection = 'mysql';

}
```
#### Option 2 - `HasSettingsTable` trait <a name="update_models_2"></a>
Run before the command `php artisan model-settings:model-settings-table`.\
The command will copy for you the migration class to create the table where the setting values will be stored.\
The default name of the table is `model_settings`; change the config or env value `MODEL_SETTINGS_TABLE_NAME` if you want to rewrite the default name (**before you run the command!**)
```php
use Glorand\Model\Settings\Traits\HasSettingsTable;

class User extends Model
{
    use HasSettingsTable;
}
```

#### Option 3 - `HasSettingsRedis` trait <a name="update_models_3"></a>
```php
use Glorand\Model\Settings\Traits\HasSettingsRedis;

class User extends Model
{
    use HasSettingsRedis;
}
```

## Default settings <a name="default_settings"></a>

You can set default configs for a table in model_settings.php config file

```php
return [
    // start other config options

    // end other config options

    // defaultConfigs
    'defaultSettings' => [
        'users' => [
            'key_1' => 'val_1',
        ]
    ]
];
```

Or in your model itself:

```php
use Glorand\Model\Settings\Traits\HasSettingsTable;

class User extends Model
{
    public $defaultSettings = [
        'key_1' => 'val_1',
    ];
}
```

> Please note that if you define settings in the model, the settings from configs will have no effect, they will just be ignored.


## Usage <a name="usage"></a>

```php
$user = App\User::first();
```

#### Check if the settings for the entity is empty <a name="empty"></a>
```php
$user->settings()->empty();
```

#### Check settings (exist) <a name="exist"></a>
```php
$user->settings()->exist();
```

#### Get all model's settings <a name="get_all"></a>
```php
$user->settings()->all();
$user->settings()->get();
```

#### Get a specific setting <a name="get"></a>
```php
$user->settings()->get('some.setting');
$user->settings()->get('some.setting', 'default value');
//multiple
$user->settings()->getMultiple(
	[
		'some.setting_1',
		'some.setting_2',
	],
	'default value'
);
```

#### Add / Update setting <a name="add_update"></a>
```php
$user->settings()->apply((array)$settings);
$user->settings()->set('some.setting', 'new value');
$user->settings()->update('some.setting', 'new value');
//multiple
$user->settings()->setMultiple([
	'some.setting_1' => 'new value 1',
	'some.setting_2' => 'new value 2',
]);
```

#### Check if the model has a specific setting <a name="check"></a>
```php
$user->settings()->has('some.setting');
```

#### Remove a setting from a model <a name="remove"></a>
```php
$user->settings()->delete('some.setting');
//multiple
$user->settings()->deleteMultiple([
	'some.setting_1',
	'some.setting_2',
]);
//all
$user->settings()->clear();
```

#### Persistence for settings field <a name="persistence"></a>
In case of field settings the auto-save is configurable.

**The ``default`` value is ``true``**

 - Use an attribute on model
```php
protected $persistSettings = true; //boolean
```
 - Environment (.env) variable
 ```dotenv
MODEL_SETTINGS_PERSISTENT=true
```
- Config value - model settings config file
 ```php
'settings_persistent' => env('MODEL_SETTINGS_PERSISTENT', true),
```
If the persistence is `false` you have to save the model after the operation.

### Using another method name other than `settings()` <a name="invokeSettingsBy"></a>
If you prefer to use another name other than `settings` ,
you can do so by defining a `$invokeSettingsBy` property. 
This forward calls (such as `configurations()`) to the `settings()` method.

### Validation system for settings data <a name="validation"></a>
When you're using the set() or apply()|update() methods thrown an exception when you break a rule.
You can define rules on model using `$settingsRules` public property, and the rules array definition is identical with
the Laravel default validation rules. ([see Laravel rules](https://laravel.com/docs/8.x/validation#available-validation-rules))
```php
class User extends Model
{
    use HasSettingsTable;

    public array $defaultSettings = [
        'user' => [
            'name' => 'Test User',
            'email' => 'user@test.com'
            'age' => 27,
        ],
        'language' => 'en',
        'max_size' => 12,
    ];

    // settings rules
    public array $settingsRules = [
        'user' => 'array',
        'user.email' => [
            'string',
            'email',
        ],
        'user.age' => 'integer',
        'language' => 'string|in:en,es,it|max:2',
        'max_size' => 'int|min:5|max:15',
    ];
}

```

## Changelog <a name="changelog"></a>
Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing <a name="contributing"></a>
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License <a name="license"></a>
The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

## Related Stuff
- [LaraNews - Laravel Model Settings](https://laravel-news.com/laravel-model-settings)
- [made with Laravel - Laravel Model Settings](https://madewithlaravel.com/laravel-model-settings)
