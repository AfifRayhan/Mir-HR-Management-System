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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('spouse_name', 150)->nullable()->after('mother_name');
            $table->string('gender', 20)->nullable()->after('spouse_name');
            $table->string('religion', 50)->nullable()->after('gender');
            $table->string('marital_status', 50)->nullable()->after('religion');
            $table->string('national_id', 50)->nullable()->after('marital_status');
            $table->string('tin', 50)->nullable()->after('national_id');
            $table->integer('no_of_children')->nullable()->after('tin');
            $table->string('contact_no', 100)->nullable()->after('no_of_children');
            $table->string('emergency_contact_name', 150)->nullable()->after('blood_group');
            $table->text('emergency_contact_address')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_no', 100)->nullable()->after('emergency_contact_address');
            $table->string('emergency_contact_relation', 50)->nullable()->after('emergency_contact_no');
            $table->text('present_address')->nullable()->after('phone');
            $table->text('permanent_address')->nullable()->after('present_address');
            $table->date('discontinuation_date')->nullable()->after('joining_date');
            $table->text('discontinuation_reason')->nullable()->after('discontinuation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'spouse_name', 'gender', 'religion', 'marital_status', 
                'national_id', 'tin', 'no_of_children', 'contact_no',
                'emergency_contact_name', 'emergency_contact_address', 
                'emergency_contact_no', 'emergency_contact_relation',
                'present_address', 'permanent_address',
                'discontinuation_date', 'discontinuation_reason'
            ]);
        });
    }
};
