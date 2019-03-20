<?php

namespace Glorand\Model\Settings\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class WrongUser extends Model
{
    protected $table = 'wrong_users';

    protected $guarded = [];

    protected $fillable = ['id', 'name'];
}
