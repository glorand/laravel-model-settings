<?php

namespace Glorand\Model\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property array $settings
 */
class ModelSettings extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('model_settings.drivers.table.table_name', 'model_settings'));
    }

    protected $casts = [
        'settings' => 'json',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
