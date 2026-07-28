<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ModuleDefinition extends Model
{
    use HasFactory;

    protected $table = 'module_definitions';

    protected $fillable = [
        'tenant_id',
        'slug',
        'name',
        'description',
        'path',
        'version',
        'is_core',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_core' => 'boolean',
            'is_enabled' => 'boolean',
        ];
    }

    public function settings(): HasMany
    {
        return $this->hasMany(ModuleSetting::class);
    }
}
