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
        // Drop old permission system
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');

        // Create menu_items table
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // Display label, e.g. "Dashboard"
            $table->string('slug')->unique();  // Unique key, e.g. "dashboard"
            $table->string('icon')->nullable(); // Bootstrap Icons class, e.g. "bi-speedometer2"
            $table->string('route_name')->nullable(); // Laravel route name, e.g. "hr-dashboard"
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Create role_menu_item pivot table
        Schema::create('role_menu_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'menu_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_menu_item');
        Schema::dropIfExists('menu_items');

        // Recreate old permission tables
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('module')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });
    }
};
