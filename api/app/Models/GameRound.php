<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameRound extends Model
{
    protected $fillable = [
        'room_id', 'status', 'current_number', 'pool_json', 'drawn_json', 'started_at', 'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'pool_json' => 'array',
            'drawn_json' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class, 'round_id');
    }
}
