<?php

namespace Glorand\Model\Settings\Tests\Models;

class UsersWithParentModelWithField extends UserWithTextField
{
    protected $table = 'users_with_field';

    protected $fillable = ['id', 'name'];
}