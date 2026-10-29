<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Room;
use App\Models\User;
use App\Models\Winner;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function overview(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return response()->json([
            'stats' => [
                'users' => User::count(),
                'live' => Room::where('status', 'LIVE')->count(),
                'scheduled' => Room::where('status', 'SCHEDULED')->count(),
                'ended' => Room::where('status', 'ENDED')->count(),
            ],
            'rooms' => Room::with('host')->latest()->get()->map(fn ($r) => array_merge($r->toPublicArray(), [
                'hostNickname' => $r->host?->displayName(),
            ])),
            'users' => User::latest()->get(['id', 'name', 'nickname', 'email', 'role', 'status', 'points', 'created_at']),
            'logs' => AuditLog::latest()->limit(40)->get(),
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $data = $request->validate([
            'status' => 'nullable|in:ACTIVE,LOCKED',
            'role' => 'nullable|in:USER,HOST,ADMIN',
            'points' => 'nullable|integer|min:0',
        ]);
        $user->fill(array_filter($data, fn ($v) => $v !== null))->save();
        AuditLog::record($request->user()->id, 'ADMIN_USER_UPDATE', 'user', (string) $user->id, $data);

        return response()->json(['user' => $user->toPublicArray()]);
    }

    public function updateRoom(Request $request, Room $room)
    {
        abort_unless($request->user()?->isAdmin(), 403);
        $data = $request->validate([
            'status' => 'nullable|in:SCHEDULED,LIVE,ENDED',
            'streamStatus' => 'nullable|in:IDLE,LIVE,ENDED',
        ]);
        if (isset($data['status'])) {
            $room->status = $data['status'];
        }
        if (isset($data['streamStatus'])) {
            $room->stream_status = $data['streamStatus'];
        }
        $room->save();
        AuditLog::record($request->user()->id, 'ADMIN_ROOM_UPDATE', 'room', (string) $room->id, $data);

        return response()->json(['room' => $room->toPublicArray()]);
    }

    public function hostRooms(Request $request)
    {
        $user = $request->user();
        abort_unless($user?->isHost(), 403);

        $rooms = Room::with('host')
            ->when($user->role === 'HOST', fn ($q) => $q->where('host_id', $user->id))
            ->latest()
            ->get()
            ->map->toPublicArray();

        return response()->json(['rooms' => $rooms]);
    }

    public function leaderboard()
    {
        $winCounts = Winner::query()
            ->selectRaw('user_id, count(*) as c')
            ->groupBy('user_id')
            ->pluck('c', 'user_id');

        $rows = User::where('role', '!=', 'ADMIN')
            ->orderByDesc('points')
            ->limit(50)
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'nickname' => $u->displayName(),
                'points' => $u->points,
                'wins' => (int) ($winCounts[$u->id] ?? 0),
            ])
            ->sortByDesc(fn ($r) => [$r['wins'], $r['points']])
            ->values()
            ->take(20)
            ->values()
            ->map(function ($r, $i) {
                $r['rank'] = $i + 1;

                return $r;
            });

        return response()->json(['leaders' => $rows]);
    }
}
