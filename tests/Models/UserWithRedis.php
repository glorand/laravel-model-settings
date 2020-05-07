<?php

namespace Glorand\Model\Settings\Tests\Models;

use Glorand\Model\Settings\Traits\HasSettingsRedis;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserWithRedis
 * @package Glorand\Model\Settings\Tests\Models
 * @method static first()
 */
class UserWithRedis extends Model
{
    use HasSettingsRedis;

    protected $table = 'users';

    protected $guarded = [];

    protected $fillable = ['id', 'name'];

    public $defaultSettings = [];
}
