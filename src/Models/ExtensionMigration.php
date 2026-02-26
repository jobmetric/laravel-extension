<?php

namespace JobMetric\Extension\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Tracks extension migration runs (same idea as Laravel migrations table). id, extension, name, migration, created_at.
 *
 * @package JobMetric\Extension
 *
 * @property int $id
 * @property string $extension
 * @property string $name
 * @property string $migration
 * @property Carbon|null $created_at
 *
 * @method static Builder|ExtensionMigration whereExtension(string $extension)
 * @method static Builder|ExtensionMigration whereName(string $name)
 * @method static Builder|ExtensionMigration whereMigration(string $migration)
 * @method static ExtensionMigration|null find(int $id)
 */
class ExtensionMigration extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'extension',
        'name',
        'migration',
    ];

    protected $casts = [
        'extension'  => 'string',
        'name'       => 'string',
        'migration'  => 'string',
        'created_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('extension.tables.extension_migration', parent::getTable());
    }
}
