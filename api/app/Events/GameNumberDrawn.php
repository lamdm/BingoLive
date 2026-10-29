<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameNumberDrawn implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $roomId,
        public int $roundId,
        public int $number,
        public int $sequence,
        public array $drawn,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('room.'.$this->roomId)];
    }

    public function broadcastAs(): string
    {
        return 'game.number-drawn';
    }
}
