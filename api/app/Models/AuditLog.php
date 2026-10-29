<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['actor_id', 'action', 'resource_type', 'resource_id', 'metadata_json'];

    protected function casts(): array
    {
        return ['metadata_json' => 'array'];
    }

    public static function record(?int $actorId, string $action, ?string $type = null, ?string $id = null, mixed $meta = null): void
    {
        static::create([
            'actor_id' => $actorId,
            'action' => $action,
            'resource_type' => $type,
            'resource_id' => $id,
            'metadata_json' => $meta,
        ]);
    }
}
