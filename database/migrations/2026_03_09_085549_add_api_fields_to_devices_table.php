<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('device_uid', 50)->unique()->after('name')->nullable();
            $table->string('api_token', 80)->unique()->after('device_uid')->nullable();
            $table->timestamp('last_sync_at')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['device_uid', 'api_token', 'last_sync_at']);
        });
    }
};
