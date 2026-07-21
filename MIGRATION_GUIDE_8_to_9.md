# Migration Guide: v8 → v9

Version 9 replaces the three storage-specific traits with a **single `HasSettings` trait** and
a **configuration-driven driver system**, following the same pattern as Laravel's own
`CacheManager` / `FilesystemManager`. It also raises the platform requirements and
restructures the config file.

This guide covers everything needed to move an application from `^8.0` to `^9.0`.

---

## Requirements

| | v8 | v9 |
|---|---|---|
| PHP | `^8.1` | **`^8.2`** |
| Laravel | 10 - 13 | **12 - 13** |

> Laravel 13 itself requires PHP ≥ 8.3.

If your application is on Laravel 10/11 or PHP 8.1, upgrade the framework first, or stay on
`glorand/laravel-model-settings:^8.0`.

---

## TL;DR checklist

1. [ ] PHP ≥ 8.2 and Laravel ≥ 12
2. [ ] Swap `HasSettingsField` / `HasSettingsTable` / `HasSettingsRedis` → `HasSettings`
3. [ ] Declare the driver on each model (`$settingsDriver`) or app-wide (`MODEL_SETTINGS_DRIVER`)
   - **critical for table/redis models, the default is `field`**
4. [ ] Re-publish or migrate `config/model_settings.php` (the flat `settings_*` keys are gone)
5. [ ] Done - the rest of the API is unchanged

---

## 1. Replace the trait

There is now exactly one trait. The storage backend is picked by the driver, not the trait.

```diff
 use Illuminate\Database\Eloquent\Model;
-use Glorand\Model\Settings\Traits\HasSettingsField;   // or HasSettingsTable / HasSettingsRedis
+use Glorand\Model\Settings\Traits\HasSettings;

 class User extends Model
 {
-    use HasSettingsField;
+    use HasSettings;
 }
```

The removed classes:

- `Glorand\Model\Settings\Traits\HasSettingsField`
- `Glorand\Model\Settings\Traits\HasSettingsTable`
- `Glorand\Model\Settings\Traits\HasSettingsRedis`

## 2. Declare the driver

The driver defaults to **`field`**. A former `HasSettingsTable` / `HasSettingsRedis` model
that does not declare its driver will silently read and write through the `field` driver,
so this step is **mandatory** for those models.

Per model:

```php
class User extends Model
{
    use HasSettings;

    protected $settingsDriver = 'table'; // 'field' (default) | 'table' | 'redis'
}
```

Or app-wide, in `.env`:

```dotenv
MODEL_SETTINGS_DRIVER=table
```

The per-model property always wins over the config/env value.

## 3. Migrate the config

The config file is restructured: every driver-specific key now lives under
`drivers.<name>.*`. The old flat keys are **deleted and never read**; there is no fallback.

The simplest path is to re-publish:

```bash
php artisan vendor:publish --provider="Glorand\Model\Settings\ModelSettingsServiceProvider"
```

If you customized the published file, move your values:

| v8 key | v9 key |
|--------|--------|
| `settings_field_name` | `drivers.field.field_name` |
| `settings_persistent` | `drivers.field.persistent` |
| `settings_table_name` | `drivers.table.table_name` |
| `settings_table_use_cache` | `drivers.table.use_cache` |
| `settings_table_cache_prefix` | `drivers.table.cache_prefix` |
| `defaultSettings` | unchanged (stays top-level) |

**All env variable names are unchanged** (`MODEL_SETTINGS_FIELD_NAME`,
`MODEL_SETTINGS_TABLE_NAME`, `MODEL_SETTINGS_PERSISTENT`, …). If you only configure through
`.env`, re-publishing the config file is all you need.

The new file shape:

```php
return [
    'driver' => env('MODEL_SETTINGS_DRIVER', 'field'),

    'drivers' => [
        'field' => [
            'class' => \Glorand\Model\Settings\Managers\FieldSettingsManager::class,
            'field_name' => env('MODEL_SETTINGS_FIELD_NAME', 'settings'),
            'persistent' => env('MODEL_SETTINGS_PERSISTENT', true),
            'cache_prefix' => env('MODEL_SETTINGS_FIELD_CACHE_PREFIX', 'model_settings:'),
        ],
        'table' => [
            'class' => \Glorand\Model\Settings\Managers\TableSettingsManager::class,
            'table_name' => env('MODEL_SETTINGS_TABLE_NAME', 'model_settings'),
            'use_cache' => env('MODEL_SETTINGS_TABLE_USE_CACHE', true),
            'cache_prefix' => env('MODEL_SETTINGS_TABLE_CACHE_PREFIX', 'model_settings:'),
        ],
        'redis' => [
            'class' => \Glorand\Model\Settings\Managers\RedisSettingsManager::class,
            'connection' => env('MODEL_SETTINGS_REDIS_CONNECTION'), // null = default connection
            'key_prefix' => env('MODEL_SETTINGS_REDIS_PREFIX', 'r-k-'),
        ],
    ],

    'defaultSettings' => [
        'users' => [],
    ],
];
```

## 4. What does NOT change

The whole runtime API is preserved. No call-site changes are needed for:

- `$model->settings()` and every manager method: `get()`, `set()`, `update()`, `has()`,
  `all()`, `getMultiple()`, `setMultiple()`, `delete()`, `deleteMultiple()`, `clear()`,
  `exist()`, `empty()`
- Direct assignment on field models: `$model->settings = [...]` (the `saving` hook still
  JSON-encodes it)
- `$model->setPersistSettings(false)` / the `$persistSettings` property (field driver)
- `$model->modelSettings()` MorphOne relation and `$model->getSettingsCacheKey()` (table driver)
- `$model->cacheKey()` (redis driver)
- Per-model overrides: `$settingsFieldName`, `$defaultSettings`, `$settingsRules`,
  `$invokeSettingsBy`
- Default settings merging, validation via `$settingsRules`, table-backend caching

## 5. Behavioral changes to be aware of

- **Mutations no longer persist defaults.** In v8, `set()`, `update()`, `delete()`,
  `setMultiple()` and `deleteMultiple()` started from the default-merged settings, so a single
  `set()` silently copied every default value into storage - freezing them there even if the
  configured defaults changed later. In v9 these methods start from the stored value and
  persist only the overrides; defaults stay in config/model and are merged at read time.
  Validation still runs against the effective result (defaults + proposed values).
  `apply()` is unchanged: it persists exactly the array you pass it.
- **The `table` and `redis` drivers require a saved model.** Reading or writing settings on a
  model without a primary key throws a `ModelSettingsException` instead of silently producing
  rows or Redis keys with a `null` key. Save the model first.
- **Clearing Redis settings deletes the key.** The `redis` driver removes the storage key when
  the settings become empty instead of storing `[]`, matching the `table` driver's
  delete-the-row behavior.
- **Unknown driver throws.** An unregistered driver name (typo in `$settingsDriver` or
  `MODEL_SETTINGS_DRIVER`) throws a `ModelSettingsException` instead of silently misbehaving.
- **Field schema-check cache fix.** The internal cache key that remembers whether the settings
  column exists now includes the table name. In v8, two field-driver models on different
  tables shared one cached result (a long-standing bug). After upgrading, this cache entry is
  simply rebuilt; no action needed.
- **Stale config degrades gracefully.** Every driver config read carries an in-code default
  (`'settings'`, `true`, `'model_settings:'`, …), so a forgotten config migration falls back
  to default behavior rather than throwing - but migrate it anyway (step 3).

## 6. New in v9

### Custom drivers

Register your own storage backend without touching the package, either statically in the
config:

```php
'drivers' => [
    // ...
    'dynamodb' => [
        'class' => \App\Settings\DynamoDbSettingsManager::class, // extends AbstractSettingsManager
    ],
],
```

…or at runtime, e.g. in a service provider:

```php
use Glorand\Model\Settings\SettingsManagerFactory;

app(SettingsManagerFactory::class)->extend('dynamodb', function ($model) {
    return new DynamoDbSettingsManager($model);
});
```

Then point any model at it: `protected $settingsDriver = 'dynamodb';`. A custom driver gets
its own `drivers.<name>.*` config namespace automatically.

### Redis driver options

Two new optional keys (both default to the v8 behavior):

- `drivers.redis.connection` - a named Redis connection (`null` = default connection)
- `drivers.redis.key_prefix` - the storage key prefix (previously hardcoded `r-k-`)

---

## Troubleshooting

**A table/redis model reads empty settings after the upgrade, and writes appear in the model's
own table (or a `settings` column error is thrown).**
The model is running on the default `field` driver - declare `protected $settingsDriver`
(step 2).

**`ModelSettingsException: Unknown field (settings) on table …`**
The `field` driver is active but the table has no settings column. Either this model should
use another driver (step 2), or the column is missing - generate it with
`php artisan model-settings:model-settings-field`.

**`ModelSettingsException: Unsupported settings driver [xyz]`**
Typo in the driver name, or a custom driver was not registered in `drivers` / via `extend()`.

**My published config still has the old keys.**
They are ignored (built-in defaults apply). Migrate them with the table in step 3.
