<?php

namespace App\Http\Controllers\Api;

use App\Bingo\BingoEngine;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BingoCard;
use App\Models\Room;
use App\Models\RoomParticipant;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $q = Room::with('host')->latest();

        $status = strtoupper((string) $request->query('status', ''));
        if (in_array($status, ['LIVE', 'SCHEDULED', 'ENDED'], true)) {
            $q->where('status', $status);
        }

        $entry = strtoupper((string) $request->query('entryType', ''));
        if (in_array($entry, ['FREE', 'PAID'], true)) {
            $q->where('entry_type', $entry);
        }

        if ($search = trim((string) $request->query('q', ''))) {
            $q->where('name', 'like', "%{$search}%");
        }

        return response()->json([
            'rooms' => $q->get()->map->toPublicArray()->values(),
        ]);
    }

    public function show(Room $room)
    {
        $room->load('host', 'latestRound');

        return response()->json([
            'room' => array_merge($room->toPublicArray(), [
                'participantCount' => $room->participants()->where('status', 'JOINED')->count(),
                'round' => $room->latestRound ? [
                    'id' => $room->latestRound->id,
                    'status' => $room->latestRound->status,
                    'currentNumber' => $room->latestRound->current_number,
                    'drawn' => $room->latestRound->drawn_json ?? [],
                ] : null,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_unless($user?->isHost(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'nullable|string|max:500',
            'entryType' => 'required|in:FREE,PAID',
            'entryFee' => 'nullable|integer|min:10|max:5000',
        ]);

        $room = Room::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'host_id' => $user->id,
            'status' => 'SCHEDULED',
            'entry_type' => $data['entryType'],
            'entry_fee' => $data['entryType'] === 'PAID' ? ($data['entryFee'] ?? 50) : 0,
            'bingo_rule' => ['row', 'column', 'diagonal'],
            'scheduled_at' => now(),
            'stream_status' => 'IDLE',
        ]);

        AuditLog::record($user->id, 'ROOM_CREATE', 'room', (string) $room->id);

        return response()->json(['room' => $room->load('host')->toPublicArray()], 201);
    }

    public function join(Request $request, Room $room)
    {
        $user = $request->user();
        abort_if($room->status === 'ENDED', 400, 'Phòng đã kết thúc');

        $participant = RoomParticipant::firstOrNew([
            'room_id' => $room->id,
            'user_id' => $user->id,
        ]);

        if (! $participant->exists) {
            if ($room->entry_type === 'PAID') {
                if ($user->points < $room->entry_fee) {
                    return response()->json([
                        'message' => "Không đủ điểm ảo (cần {$room->entry_fee}, bạn có {$user->points})",
                    ], 400);
                }
                $user->decrement('points', $room->entry_fee);
                $user->refresh();
            }
            $participant->status = 'JOINED';
            $participant->joined_at = now();
            $participant->save();
            $room->increment('viewer_count');
        } else {
            $participant->update(['status' => 'JOINED', 'left_at' => null]);
        }

        $card = BingoCard::firstOrCreate(
            ['room_id' => $room->id, 'user_id' => $user->id],
            ['card_number' => 1, 'numbers_json' => BingoEngine::generateCard()],
        );

        AuditLog::record($user->id, 'ROOM_JOIN', 'room', (string) $room->id);

        return response()->json([
            'ok' => true,
            'user' => $user->fresh()->toPublicArray(),
            'card' => [
                'id' => $card->id,
                'cardNumber' => $card->card_number,
                'numbers' => $card->numbers_json,
            ],
        ]);
    }

    public function liveState(Request $request, Room $room)
    {
        $room->load('host', 'latestRound');
        $user = $request->user();
        $card = null;
        if ($user) {
            $card = BingoCard::where('room_id', $room->id)->where('user_id', $user->id)->first();
        }

        $messages = $room->messages()
            ->with('user')
            ->where('status', 'VISIBLE')
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($m) => [
                'id' => $m->id,
                'userId' => $m->user_id,
                'nickname' => $m->user->displayName(),
                'role' => $m->user->role,
                'content' => $m->content,
                'createdAt' => $m->created_at?->toIso8601String(),
            ]);

        $participants = $room->participants()
            ->with('user')
            ->where('status', 'JOINED')
            ->limit(30)
            ->get()
            ->map(fn ($p) => [
                'userId' => $p->user_id,
                'nickname' => $p->user->displayName(),
                'points' => $p->user->points,
            ]);

        $round = $room->latestRound;

        return response()->json([
            'room' => $room->toPublicArray(),
            'round' => $round ? [
                'id' => $round->id,
                'status' => $round->status,
                'currentNumber' => $round->current_number,
                'drawn' => $round->drawn_json ?? [],
            ] : null,
            'card' => $card ? [
                'id' => $card->id,
                'numbers' => $card->numbers_json,
            ] : null,
            'messages' => $messages,
            'participants' => $participants,
            'stream' => [
                'status' => $room->stream_status,
                'message' => $room->stream_status === 'LIVE'
                    ? 'Livestream demo — chưa gắn provider thật (LiveKit/Mux).'
                    : 'Stream idle',
            ],
        ]);
    }

    public function result(Room $room)
    {
        $winners = $room->winners()->with(['user', 'card'])->orderBy('verified_at')->get()->map(fn ($w) => [
            'id' => $w->id,
            'nickname' => $w->user->displayName(),
            'pattern' => $w->winning_pattern,
            'pointsAwarded' => $w->points_awarded,
            'numbers' => $w->card->numbers_json,
            'verifiedAt' => $w->verified_at?->toIso8601String(),
        ]);

        return response()->json([
            'room' => $room->toPublicArray(),
            'winners' => $winners,
        ]);
    }
}
