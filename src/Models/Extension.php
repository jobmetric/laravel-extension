<?php

namespace JobMetric\Extension\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class Extension
 *
 * Represents an extension definition that can have multiple plugins.
 * Extensions are identified by their type, name, and namespace.
 * Each extension can contain configuration info and manage its plugins.
 *
 * @package JobMetric\Extension
 *
 * @property int $id               The primary identifier of the extension row.
 * @property string $extension     The type/category of the extension.
 * @property string $name          The unique name of the extension.
 * @property string $namespace     The PHP namespace for the extension class.
 * @property array|null $info      Optional JSON configuration/metadata for the extension.
 * @property Carbon $created_at    The timestamp when this extension was created.
 * @property Carbon $updated_at    The timestamp when this extension was last updated.
 *
 * @property-read Plugin[] $plugins
 * @property-read int $plugin_count
 *
 * @method static Builder|Extension ofExtensionName(string $extension, string $name)
 * @method static Builder|Extension ofNamespace(string $namespace)
 * @method static Builder|Extension whereExtension(string $extension)
 * @method static Builder|Extension whereName(string $name)
 * @method static Builder|Extension whereNamespace(string $namespace)
 * @method static Extension|null find(int|null $extension_id)
 * @method static Extension create(array $attributes)
 */
class Extension extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'extension',
        'name',
        'namespace',
        'info',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'extension' => 'string',
        'name'      => 'string',
        'namespace' => 'string',
        'info'      => 'array',
    ];

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
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
        return $this->hasMany(Plugin::class, 'extension_id');
    }

    /**
     * Scope: filter by extension type and name.
     *
     * @param Builder $query
     * @param string $extension
     * @param string $name
     *
     * @return Builder
     */
    public function scopeOfExtensionName(Builder $query, string $extension, string $name): Builder
    {
        return $query->where([
            'extension' => $extension,
            'name'      => $name,
        ]);
    }

    /**
     * Scope: filter by namespace.
     *
     * @param Builder $query
     * @param string $namespace
     *
     * @return Builder
     */
    public function scopeOfNamespace(Builder $query, string $namespace): Builder
    {
        return $query->where('namespace', $namespace);
    }

    /**
     * Accessor: get the count of plugins for this extension.
     *
     * @return int
     */
    public function getPluginCountAttribute(): int
    {
        return $this->plugins()->count();
    }
}
