<?php

namespace Glorand\Model\Settings\Tests\Models;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UsersWithTable
 * @package Glorand\Model\Settings\Tests\Models
 * @method static first()
 * @method SettingsManagerContract config()
 */
class UsersWithTable extends Model
{
    use HasSettings;

    protected $settingsDriver = 'table';

    public $invokeSettingsBy = 'config';

    protected $table = 'users_with_table';

    protected $guarded = [];

    protected $fillable = ['id', 'name'];

    public $defaultSettings = [];

    public $settingsRules = [];
}
