<?php

namespace Glorand\Model\Settings\Tests\Models;

use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserWithCache
 * @package Glorand\Model\Settings\Tests\Models
 * @method static first()
 */
class UserWithCache extends Model
{
    use HasSettings;

    protected $settingsDriver = 'cache';

    protected $table = 'users';

    protected $guarded = [];

    protected $fillable = ['id', 'name'];

    public $defaultSettings = [];

    public $settingsRules = [];
}
