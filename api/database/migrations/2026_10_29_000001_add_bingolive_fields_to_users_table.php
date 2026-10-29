<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nickname')->nullable()->after('name');
            $table->string('role')->default('USER')->after('password'); // USER|HOST|ADMIN
            $table->string('status')->default('ACTIVE')->after('role');
            $table->unsignedInteger('points')->default(500)->after('status');
            $table->string('avatar_url')->nullable()->after('points');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nickname', 'role', 'status', 'points', 'avatar_url']);
        });
    }
};
