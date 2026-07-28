<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ModuleSetting extends Model
{
    use HasFactory;

    protected $table = 'module_settings';

    protected $fillable = [
        'tenant_id',
        'module_definition_id',
        'key',
        'value',
        'type',
        'is_secret',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'is_secret' => 'boolean',
        ];
    }

    public function moduleDefinition(): BelongsTo
    {
        return $this->belongsTo(ModuleDefinition::class);
    }
}
