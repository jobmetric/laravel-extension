<?php

namespace JobMetric\Extension\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use JobMetric\PackageCore\Models\HasBooleanStatus;

/**
 * Class Plugin
 *
 * Represents a plugin instance that belongs to an extension.
 * Plugins can store custom field configurations and can be enabled/disabled.
 * Each plugin is associated with exactly one extension.
 *
 * @package JobMetric\Extension
 *
 * @property int $id               The primary identifier of the plugin row.
 * @property int $extension_id     The owning extension identifier.
 * @property string $name          The unique name of the plugin within the extension.
 * @property array|null $fields    Optional JSON configuration/fields for the plugin.
 * @property bool $status          Active flag (true=enabled, false=disabled).
 * @property Carbon $created_at    The timestamp when this plugin was created.
 * @property Carbon $updated_at    The timestamp when this plugin was last updated.
 *
 * @property-read Extension $extension
 *
 * @method static Builder|Plugin whereExtensionId(int $extension_id)
 * @method static Builder|Plugin whereName(string $name)
 * @method static Builder|Plugin whereStatus(bool $status)
 * @method static Plugin|null find(int $plugin_id)
 * @method static Plugin create(array $attributes)
 */
class Plugin extends Model
{
    use HasFactory, HasBooleanStatus;

    /**
     * Touch the parent extension when this plugin is updated.
     *
     * @var array<int, string>
     */
    protected $touches = ['extension'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'extension_id',
        'name',
        'fields',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'extension_id' => 'integer',
        'name'         => 'string',
        'fields'       => AsArrayObject::class,
        'status'       => 'boolean',
    ];

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
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
        return $this->belongsTo(Extension::class, 'extension_id');
    }
}
