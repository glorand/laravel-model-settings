<?php

namespace Glorand\Model\Settings\Tests\Models;

use Glorand\Model\Settings\Traits\HasSettingsTable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UsersWithTable
 * @package Glorand\Model\Settings\Tests\Models
 * @method static first()
 */
class UsersWithTable extends Model
{
    use HasSettingsTable;

    protected $table = 'users_with_table';

    protected $guarded = [];

    protected $fillable = ['id', 'name'];

    public $defaultSettings = [];
}
