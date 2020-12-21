<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Tests\Models\UserWithRedis as User;

class RedisSettingsManagerTest extends TestCase
{
    /** @var \Glorand\Model\Settings\Tests\Models\UserWithRedis */
    protected $model;

    public function setUp(): void
    {
        parent::setUp();
        $this->model = User::first();
    }

    public function testMarker()
    {
        $this->assertTrue(true);
    }
}
