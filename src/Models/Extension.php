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
 * @property string namespace
 * @property array info
 * @property int plugin_count
 * @property Carbon created_at
 * @property Carbon updated_at
 * @method static ExtensionName(string $extension, string $name)
 * @method static ExtensionNamespace(string $namespace)
 * @method static create(array $array)
 */
class Extension extends Model
{
    use HasFactory;

    protected $fillable = [
        'extension',
        'name',
        'namespace',
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
        'namespace' => 'string',
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
     * Scope a query to only include the namespace.
     *
     * @param Builder $query
     * @param string $namespace
     *
     * @return Builder
     */
    public function scopeExtensionNamespace(Builder $query, string $namespace): Builder
    {
        return $query->where([
            'namespace' => $namespace
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
