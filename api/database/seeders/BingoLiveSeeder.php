<?php

namespace Database\Seeders;

use App\Bingo\BingoEngine;
use App\Models\GameRound;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BingoLiveSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@bingolive.local'],
            [
                'name' => 'Admin Bingo',
                'nickname' => 'Admin Bingo',
                'password' => Hash::make('password123'),
                'role' => 'ADMIN',
                'status' => 'ACTIVE',
                'points' => 9999,
            ],
        );

        $host = User::updateOrCreate(
            ['email' => 'host@bingolive.local'],
            [
                'name' => 'Host Lan',
                'nickname' => 'Host Lan',
                'password' => Hash::make('password123'),
                'role' => 'HOST',
                'status' => 'ACTIVE',
                'points' => 2000,
            ],
        );

        User::updateOrCreate(
            ['email' => 'player@bingolive.local'],
            [
                'name' => 'Người chơi Demo',
                'nickname' => 'Người chơi Demo',
                'password' => Hash::make('password123'),
                'role' => 'USER',
                'status' => 'ACTIVE',
                'points' => 800,
            ],
        );

        $free = Room::updateOrCreate(
            ['name' => 'Bingo đêm hội cùng Lan', 'host_id' => $host->id],
            [
                'description' => 'Phòng miễn phí — điểm ảo, không tiền thật.',
                'status' => 'LIVE',
                'entry_type' => 'FREE',
                'entry_fee' => 0,
                'bingo_rule' => ['row', 'column', 'diagonal'],
                'scheduled_at' => now(),
                'started_at' => now(),
                'viewer_count' => 128,
                'stream_status' => 'LIVE',
            ],
        );

        $paid = Room::updateOrCreate(
            ['name' => 'Party Bingo có phí điểm', 'host_id' => $host->id],
            [
                'description' => 'Vào phòng tốn điểm ảo. Thắng nhận điểm thưởng demo.',
                'status' => 'LIVE',
                'entry_type' => 'PAID',
                'entry_fee' => 50,
                'bingo_rule' => ['row', 'column', 'diagonal'],
                'scheduled_at' => now(),
                'started_at' => now(),
                'viewer_count' => 64,
                'stream_status' => 'LIVE',
            ],
        );

        Room::updateOrCreate(
            ['name' => 'Friday Party Bingo', 'host_id' => $host->id],
            [
                'description' => 'Phòng sắp diễn ra — miễn phí.',
                'status' => 'SCHEDULED',
                'entry_type' => 'FREE',
                'entry_fee' => 0,
                'bingo_rule' => ['row', 'column'],
                'scheduled_at' => now()->addHour(),
                'viewer_count' => 0,
                'stream_status' => 'IDLE',
            ],
        );

        foreach ([$free, $paid] as $room) {
            if (! $room->rounds()->where('status', 'PLAYING')->exists()) {
                GameRound::create([
                    'room_id' => $room->id,
                    'status' => 'PLAYING',
                    'pool_json' => BingoEngine::createDrawPool(),
                    'drawn_json' => [],
                    'started_at' => now(),
                ]);
            }
        }

        $this->command?->info('Demo: admin@ / host@ / player@bingolive.local · password123');
    }
}
