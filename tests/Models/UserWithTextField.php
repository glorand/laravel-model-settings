<?php

namespace Glorand\Model\Settings\Tests\Models;

use Glorand\Model\Settings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserWithTextField
 * @package Glorand\Model\Settings\Tests\Models
 * @method static first()
 */
class UserWithTextField extends Model
{
    use HasSettings;

    protected $settingsDriver = 'field';

    protected $table = 'users_with_text_field';

    protected $guarded = [];

    protected $fillable = ['id', 'name'];

    public $defaultSettings = [];

    public $settingsRules = [];
}
