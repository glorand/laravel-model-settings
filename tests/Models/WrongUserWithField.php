<?php

namespace Glorand\Model\Settings\Tests\Models;

use Glorand\Model\Settings\Traits\HasSettingsField;
use Illuminate\Database\Eloquent\Model;

/**
 * Class WrongUserWithField
 * @package Glorand\Model\Settings\Tests\Models
 * @method static first()
 */
class WrongUserWithField extends Model
{
    use HasSettingsField;

    protected $table = 'wrong_users';

    protected $guarded = [];

    protected $fillable = ['id', 'name'];
}
