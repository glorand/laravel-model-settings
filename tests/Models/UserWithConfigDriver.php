<?php

namespace Glorand\Model\Settings\Tests\Models;

use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserWithConfigDriver
 * No $settingsDriver declared - the driver always comes from the config.
 * @package Glorand\Model\Settings\Tests\Models
 * @method static first()
 */
class UserWithConfigDriver extends Model
{
    use HasSettings;

    protected $table = 'users_with_field';

    protected $guarded = [];

    protected $fillable = ['id', 'name'];

    public $defaultSettings = [];

    public $settingsRules = [];
}
