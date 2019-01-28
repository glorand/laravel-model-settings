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
    /**
     * ModelSettings constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('model_settings.settings_table_name', 'model_settings'));
    }

    protected $casts = [
        'settings' => 'json',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
