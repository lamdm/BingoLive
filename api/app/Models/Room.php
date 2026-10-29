<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Room extends Model
{
    protected $fillable = [
        'name', 'description', 'host_id', 'status', 'entry_type', 'entry_fee',
        'bingo_rule', 'scheduled_at', 'started_at', 'ended_at', 'viewer_count', 'stream_status',
    ];

    protected function casts(): array
    {
        return [
            'bingo_rule' => 'array',
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(RoomParticipant::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(BingoCard::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function latestRound(): HasOne
    {
        return $this->hasOne(GameRound::class)->latestOfMany();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'hostId' => $this->host_id,
            'host' => $this->host ? [
                'id' => $this->host->id,
                'nickname' => $this->host->nickname ?: $this->host->name,
            ] : null,
            'status' => $this->status,
            'entryType' => $this->entry_type,
            'entryFee' => $this->entry_fee,
            'bingoRule' => $this->bingo_rule,
            'scheduledAt' => optional($this->scheduled_at)?->toIso8601String(),
            'startedAt' => optional($this->started_at)?->toIso8601String(),
            'endedAt' => optional($this->ended_at)?->toIso8601String(),
            'viewerCount' => $this->viewer_count,
            'streamStatus' => $this->stream_status,
        ];
    }
}
