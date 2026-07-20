<?php

namespace Glorand\Model\Settings\Tests;

use Glorand\Model\Settings\Contracts\SettingsManagerContract;
use Glorand\Model\Settings\Managers\AbstractSettingsManager;

class CustomSettingsManager extends AbstractSettingsManager
{
    public function apply(array $settings = []): SettingsManagerContract
    {
        return $this;
    }
}
