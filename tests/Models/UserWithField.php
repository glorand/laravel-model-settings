<?php

namespace Glorand\Model\Settings\Tests\Models;

use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserWithField
 * @package Glorand\Model\Settings\Tests\Models
 * @method static first()
 */
class UserWithField extends Model
{
    use HasSettings;

    protected $settingsDriver = 'field';

    protected $table = 'users_with_field';

    protected $guarded = [];

    protected $fillable = ['id', 'name'];

    public $defaultSettings = [];

    public $settingsRules = [];
}
