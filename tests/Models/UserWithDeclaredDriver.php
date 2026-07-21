<?php

namespace Glorand\Model\Settings\Tests\Models;

/**
 * Class UserWithDeclaredDriver
 * @package Glorand\Model\Settings\Tests\Models
 */
class UserWithDeclaredDriver extends UserWithField
{
    protected $settingsDriver = 'redis';
}
