<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('SCHEDULED'); // SCHEDULED|LIVE|ENDED
            $table->string('entry_type')->default('FREE'); // FREE|PAID
            $table->unsignedInteger('entry_fee')->default(0);
            $table->json('bingo_rule');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('viewer_count')->default(0);
            $table->string('stream_status')->default('IDLE'); // IDLE|LIVE|ENDED
            $table->timestamps();
        });

        Schema::create('room_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('JOINED');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('left_at')->nullable();
            $table->unique(['room_id', 'user_id']);
        });

        Schema::create('bingo_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('card_number')->default(1);
            $table->json('numbers_json');
            $table->timestamps();
        });

        Schema::create('game_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('WAITING'); // WAITING|PLAYING|PAUSED|ENDED
            $table->unsignedTinyInteger('current_number')->nullable();
            $table->json('pool_json')->nullable();
            $table->json('drawn_json')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('content', 300);
            $table->string('status')->default('VISIBLE');
            $table->timestamps();
        });

        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('game_rounds')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_id')->constrained('bingo_cards')->cascadeOnDelete();
            $table->string('winning_pattern');
            $table->unsignedInteger('points_awarded')->default(0);
            $table->timestamp('verified_at');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('resource_type')->nullable();
            $table->string('resource_id')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('winners');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('game_rounds');
        Schema::dropIfExists('bingo_cards');
        Schema::dropIfExists('room_participants');
        Schema::dropIfExists('rooms');
    }
};
