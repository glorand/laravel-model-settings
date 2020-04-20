<?php

namespace Glorand\Model\Settings\Tests\Models;

use Closure;
use Glorand\Model\Settings\Traits\HasSettingsTable;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static whereHas(string $string, Closure $param)
 */
class UsersWithTable extends Model
{
    use HasSettingsTable;

    protected $table = 'users_with_table';

    protected $guarded = [];

    protected $fillable = ['id', 'name'];

    public $defaultSettings = [];
}
