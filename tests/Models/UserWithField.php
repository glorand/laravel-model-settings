<?php

namespace Glorand\Model\Settings\Tests\Models;

use Glorand\Model\Settings\Traits\HasSettingsField;
use Illuminate\Database\Eloquent\Model;

class UserWithField extends Model
{
    use HasSettingsField;

    protected $table = 'users_with_field';

    protected $guarded = [];

    protected $fillable = ['id', 'name'];
}
