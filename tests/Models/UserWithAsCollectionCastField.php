<?php

namespace Glorand\Model\Settings\Tests\Models;

use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;

class UserWithAsCollectionCastField extends Model
{
    use HasSettings;

    protected $settingsDriver = 'field';

    protected $table = 'users_with_field';

    protected $guarded = [];

    protected $fillable = ['id', 'name'];

    public $defaultSettings = [];

    public $settingsRules = [];

    protected function casts(): array
    {
        return [
            'settings' => AsCollection::class,
        ];
    }
}
