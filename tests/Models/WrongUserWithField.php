<?php

namespace Glorand\Model\Settings\Tests\Models;

use Glorand\Model\Settings\Traits\HasSettingsField;
use Illuminate\Database\Eloquent\Model;

class WrongUserWithField extends Model
{
    use HasSettingsField;

    protected $table = 'wrong_users';

    protected $guarded = [];

    protected $fillable = ['id', 'name'];
}
