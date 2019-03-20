<?php

namespace Glorand\Model\Settings\Traits;

trait HasSettings
{
    /**
     * @return array
     */
    abstract public function getSettingsValue(): array;
}
