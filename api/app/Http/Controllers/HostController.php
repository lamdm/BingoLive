<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HostController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user?->isHost(), 403);

        $rooms = Room::with('host')
            ->when($user->role === 'HOST', fn ($q) => $q->where('host_id', $user->id))
            ->latest()
            ->get()
            ->map->toPublicArray();

        return Inertia::render('Host/Index', [
            'rooms' => $rooms,
        ]);
    }
}
