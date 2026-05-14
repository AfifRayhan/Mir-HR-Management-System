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
        Schema::create('overtime_special_rates', function (Blueprint $table) {
            $table->id();
            $table->string('roster_group')->nullable()->index();
            $table->decimal('rate', 8, 2)->default(0);
            $table->boolean('is_eid_special')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_special_rates');
    }
};
