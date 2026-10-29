<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Winner;
use Inertia\Inertia;

class PageController extends Controller
{
    public function howToPlay()
    {
        return Inertia::render('HowToPlay');
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

        return Inertia::render('Leaderboard', ['leaders' => $rows]);
    }
}
