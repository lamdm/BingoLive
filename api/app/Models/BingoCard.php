<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BingoCard extends Model
{
    protected $fillable = ['room_id', 'user_id', 'card_number', 'numbers_json'];

    protected function casts(): array
    {
        return ['numbers_json' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
