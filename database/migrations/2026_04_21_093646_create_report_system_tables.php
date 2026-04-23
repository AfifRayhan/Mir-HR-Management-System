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
        Schema::create('report_template_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('key_tags')->nullable();
            $table->timestamps();
        });

        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_template_type_id')->constrained()->cascadeOnDelete();
            $table->string('format');
            $table->longText('content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_templates');
        Schema::dropIfExists('report_template_types');
    }
};
