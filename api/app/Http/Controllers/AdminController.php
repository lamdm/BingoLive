<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return Inertia::render('Admin/Index', [
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

        return back();
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

        return back();
    }
}
