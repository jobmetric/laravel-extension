<?php

namespace JobMetric\Extension\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * JobMetric\Extension\Models\Extension
 *
 * @property int id
 * @property string extension
 * @property string name
 * @property array info
 * @property int plugin_count
 * @property Carbon created_at
 * @property Carbon updated_at
 * @method static ExtensionName(string $extension, string $name)
 * @method static create(array $array)
 */
class Extension extends Model
{
    use HasFactory;

    protected $fillable = [
        'extension',
        'name',
        'info'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'extension' => 'string',
        'name' => 'string',
        'info' => 'array'
    ];

    public function getTable()
    {
        return config('extension.tables.extension', parent::getTable());
    }

    /**
     * Get the plugins for the extension.
     *
     * @return HasMany
     */
    public function plugins(): HasMany
    {
        return $this->hasMany(Plugin::class);
    }

    /**
     * Scope a query to only include the extension with the given name.
     *
     * @param Builder $query
     * @param string $extension
     * @param string $name
     *
     * @return Builder
     */
    public function scopeExtensionName(Builder $query, string $extension, string $name): Builder
    {
        return $query->where([
            'extension' => $extension,
            'name' => $name
        ]);
    }

    /**
     * Get plugin count
     */
    public function getPluginCountAttribute(): int
    {
        return $this->plugins()->count();
    }
}
