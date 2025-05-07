<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');

            $table->string('fullname')->nullable(); // optional, if needed for quick display
            $table->enum('attendance_status', ['present', 'absent', 'halfday'])->default('absent');
            $table->text('reason')->nullable(); // for storing absence/halfday reasons

            $table->timestamp('clock_in_time')->nullable();
            $table->timestamp('clock_out_time')->nullable();

            $table->date('date');

            $table->index('date');
            $table->index('attendance_status');
            $table->index(['staff_id', 'date']); // composite index for queries

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_attendances');
    }
};
