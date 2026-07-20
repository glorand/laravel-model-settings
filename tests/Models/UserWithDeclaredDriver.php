<?php

namespace Glorand\Model\Settings\Tests\Models;

/**
 * Class UserWithDeclaredDriver
 * @package Glorand\Model\Settings\Tests\Models
 */
class UserWithDeclaredDriver extends UserWithField
{
    public function getSettingsDriver(): string
    {
        return 'redis';
    }
}
