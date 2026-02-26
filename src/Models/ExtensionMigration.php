<?php

namespace JobMetric\Extension\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Tracks extension migration runs (same idea as Laravel migrations table). Only id and migration.
 *
 * @package JobMetric\Extension
 *
 * @property int $id
 * @property string $migration
 *
 * @method static Builder|ExtensionMigration whereMigration(string $migration)
 * @method static ExtensionMigration|null find(int $id)
 */
class ExtensionMigration extends Model
{
    public $timestamps = false;

    protected $fillable = ['migration'];

    protected $casts = [
        'migration' => 'string',
    ];

    public function getTable(): string
    {
        return config('extension.tables.extension_migration', parent::getTable());
    }
}
