<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Tests\Models\UserWithRedis as User;

final class RedisSettingsManagerTest extends TestCase
{
    /** @var \Glorand\Model\Settings\Tests\Models\UserWithRedis */
    protected $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = User::first();
    }

    public function testMarker(): void
    {
        $this->assertTrue(true);
    }
}
