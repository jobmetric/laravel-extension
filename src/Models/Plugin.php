<?php

namespace JobMetric\Extension\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use JobMetric\PackageCore\Models\HasBooleanStatus;

/**
 * JobMetric\Extension\Models\Extension
 *
 * @property int id
 * @property Extension extension
 * @property string extension_id
 * @property string title
 * @property string fields
 * @property bool status
 * @property Carbon created_at
 * @property Carbon updated_at
 * @method static find(int $plugin_id)
 */
class Plugin extends Model
{
    use HasFactory, HasBooleanStatus;

    protected $fillable = [
        'extension_id',
        'title',
        'fields',
        'status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'extension_id' => 'integer',
        'title' => 'string',
        'fields' => 'array',
        'status' => 'boolean'
    ];

    public function getTable()
    {
        return config('extension.tables.plugin', parent::getTable());
    }

    /**
     * Get the extension that owns the plugin.
     *
     * @return BelongsTo
     */
    public function extension(): BelongsTo
    {
        return $this->belongsTo(Extension::class);
    }
}
