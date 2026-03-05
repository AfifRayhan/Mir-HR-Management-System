<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // National, etc.
            $table->year('year');
            $table->string('title');
            $table->boolean('all_office')->default(false);
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->date('from_date');
            $table->date('to_date');
            $table->integer('total_days');
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
