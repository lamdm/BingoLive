<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameWinnerAnnounced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $roomId, public array $winner) {}

    public function broadcastOn(): array
    {
        return [new Channel('room.'.$this->roomId)];
    }

    public function broadcastAs(): string
    {
        return 'game.winner';
    }
}
