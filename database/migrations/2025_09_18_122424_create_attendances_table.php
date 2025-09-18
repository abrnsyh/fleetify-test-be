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
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->uuid('attendance_id')->unique();
            $table->timestamp('clock_in')->nullable();
            $table->timestamp('clock_out')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreignUuid('attendance_id')->constrained('attendances', 'attendance_id')->onDelete('cascade');
            $table->date('date_attendance');
            $table->enum('attendance_type', ['in', 'out']);
            $table->text('description')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_histories');
        Schema::dropIfExists('attendances');
    }
};
