<?php

namespace Glorand\Model\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class ModelSettings
 * @package Glorand\Model\Settings\Models
 * @property array $settings
 */
class ModelSettings extends Model
{
    protected $table = 'model_settings';

    protected $casts = [
        'settings' => 'json',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
