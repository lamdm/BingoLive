<?php

namespace App\Http\Controllers;

use App\Bingo\BingoEngine;
use App\Events\ChatMessageSent;
use App\Events\GameNumberDrawn;
use App\Events\GameWinnerAnnounced;
use App\Events\RoomStateUpdated;
use App\Models\AuditLog;
use App\Models\BingoCard;
use App\Models\ChatMessage;
use App\Models\GameRound;
use App\Models\Room;
use App\Models\RoomParticipant;
use App\Models\Winner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameController extends Controller
{
    public function startRound(Request $request, Room $room)
    {
        $this->assertHost($request, $room);

        $round = GameRound::create([
            'room_id' => $room->id,
            'status' => 'PLAYING',
            'pool_json' => BingoEngine::createDrawPool(),
            'drawn_json' => [],
            'started_at' => now(),
        ]);

        $room->update([
            'status' => 'LIVE',
            'stream_status' => 'LIVE',
            'started_at' => now(),
        ]);

        AuditLog::record($request->user()->id, 'HOST_START_ROUND', 'round', (string) $round->id);
        $this->broadcastState($room);

        return back()->with('success', 'Đã bắt đầu ván');
    }

    public function drawNext(Request $request, Room $room)
    {
        $this->assertHost($request, $room);

        $round = $room->rounds()->where('status', 'PLAYING')->latest()->first()
            ?? $room->rounds()->where('status', 'PAUSED')->latest()->first();

        abort_unless($round, 400, 'Chưa có ván đang chơi');

        $pool = $round->pool_json ?? [];
        $drawn = $round->drawn_json ?? [];

        if ($pool === []) {
            $pool = array_values(array_diff(BingoEngine::createDrawPool(), $drawn));
        }
        abort_if($pool === [], 400, 'Hết số');

        $number = array_shift($pool);
        $drawn[] = $number;

        $round->update([
            'current_number' => $number,
            'pool_json' => array_values($pool),
            'drawn_json' => $drawn,
            'status' => 'PLAYING',
        ]);

        AuditLog::record($request->user()->id, 'HOST_DRAW', 'round', (string) $round->id, [
            'number' => $number,
            'sequence' => count($drawn),
        ]);

        broadcast(new GameNumberDrawn(
            $room->id,
            $round->id,
            $number,
            count($drawn),
            $drawn,
        ))->toOthers();

        return back();
    }

    public function pause(Request $request, Room $room)
    {
        $this->assertHost($request, $room);
        $round = $room->latestRound;
        abort_unless($round, 404);
        $round->update(['status' => 'PAUSED']);
        $this->broadcastState($room);

        return back();
    }

    public function resume(Request $request, Room $room)
    {
        $this->assertHost($request, $room);
        $round = $room->latestRound;
        abort_unless($round, 404);
        $round->update(['status' => 'PLAYING']);
        $this->broadcastState($room);

        return back();
    }

    public function endRound(Request $request, Room $room)
    {
        $this->assertHost($request, $room);
        $round = $room->latestRound;
        if ($round) {
            $round->update(['status' => 'ENDED', 'ended_at' => now()]);
        }
        $room->update(['status' => 'ENDED', 'stream_status' => 'ENDED', 'ended_at' => now()]);
        AuditLog::record($request->user()->id, 'HOST_END_ROUND', 'room', (string) $room->id);
        $this->broadcastState($room);

        return back();
    }

    public function toggleStream(Request $request, Room $room)
    {
        $this->assertHost($request, $room);
        $live = (bool) $request->boolean('live');
        $room->update(['stream_status' => $live ? 'LIVE' : 'IDLE']);
        $this->broadcastState($room);

        return back();
    }

    public function claim(Request $request, Room $room)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $data = $request->validate(['cardId' => 'required|integer']);

        $participant = RoomParticipant::where('room_id', $room->id)
            ->where('user_id', $user->id)
            ->where('status', 'JOINED')
            ->first();
        abort_unless($participant, 403, 'Chưa tham gia phòng');

        $card = BingoCard::where('id', $data['cardId'])
            ->where('room_id', $room->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $round = $room->latestRound;
        abort_unless($round && in_array($round->status, ['PLAYING', 'PAUSED'], true), 400, 'Ván không đang chơi');

        if (Winner::where('round_id', $round->id)->exists()) {
            return back()->withErrors(['claim' => 'Đã có người thắng ván này']);
        }

        $check = BingoEngine::checkWin(
            $card->numbers_json,
            $round->drawn_json ?? [],
            $room->bingo_rule ?? ['row', 'column', 'diagonal'],
        );

        if (! $check['won']) {
            return back()->withErrors(['claim' => 'Chưa đủ điều kiện thắng (hàng/cột/chéo)']);
        }

        $award = $room->entry_type === 'PAID' ? max(100, $room->entry_fee * 3) : 50;

        try {
            $winner = DB::transaction(function () use ($round, $room, $user, $card, $check, $award) {
                if (Winner::where('round_id', $round->id)->lockForUpdate()->exists()) {
                    throw new \RuntimeException('RACE');
                }

                $w = Winner::create([
                    'round_id' => $round->id,
                    'room_id' => $room->id,
                    'user_id' => $user->id,
                    'card_id' => $card->id,
                    'winning_pattern' => $check['pattern'],
                    'points_awarded' => $award,
                    'verified_at' => now(),
                ]);

                $user->increment('points', $award);
                $round->update(['status' => 'ENDED', 'ended_at' => now()]);
                $room->update(['status' => 'ENDED', 'stream_status' => 'ENDED', 'ended_at' => now()]);

                return $w;
            });
        } catch (\RuntimeException) {
            return back()->withErrors(['claim' => 'Người khác vừa thắng trước']);
        }

        $payload = [
            'userId' => $user->id,
            'nickname' => $user->displayName(),
            'cardId' => $card->id,
            'pattern' => $check['pattern'],
            'pointsAwarded' => $award,
            'verifiedAt' => now()->toIso8601String(),
        ];

        AuditLog::record($user->id, 'BINGO_WIN', 'round', (string) $round->id, $payload);
        broadcast(new GameWinnerAnnounced($room->id, $payload))->toOthers();

        return redirect()->route('rooms.result', $room);
    }

    public function chat(Request $request, Room $room)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $data = $request->validate(['content' => 'required|string|max:300']);
        $content = strip_tags(trim($data['content']));
        abort_if($content === '', 422);

        $msg = ChatMessage::create([
            'room_id' => $room->id,
            'user_id' => $user->id,
            'content' => $content,
            'status' => 'VISIBLE',
        ]);

        $payload = [
            'id' => $msg->id,
            'userId' => $user->id,
            'nickname' => $user->displayName(),
            'role' => $user->role,
            'content' => $content,
            'createdAt' => $msg->created_at?->toIso8601String(),
        ];

        broadcast(new ChatMessageSent($room->id, $payload))->toOthers();

        return back();
    }

    private function assertHost(Request $request, Room $room): void
    {
        $user = $request->user();
        abort_unless($user && $user->isHost(), 403);
        if ($user->role === 'HOST') {
            abort_unless($room->host_id === $user->id, 403);
        }
    }

    private function broadcastState(Room $room): void
    {
        $room->load('host', 'latestRound');
        broadcast(new RoomStateUpdated($room->id, [
            'room' => $room->toPublicArray(),
            'round' => $room->latestRound ? [
                'id' => $room->latestRound->id,
                'status' => $room->latestRound->status,
                'currentNumber' => $room->latestRound->current_number,
                'drawn' => $room->latestRound->drawn_json ?? [],
            ] : null,
        ]))->toOthers();
    }
}
