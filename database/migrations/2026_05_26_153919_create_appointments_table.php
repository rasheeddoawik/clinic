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
        Schema::create('appointments', function (Blueprint $table) {
	    $table->id(); // يمثل الـ appointmentId
	    $table->foreignId('user_id')->constrained()->onDelete('cascade');
	    $table->foreignId('patient_id')->constrained()->onDelete('cascade');
	    $table->string('primary_physician'); // الطبيب المختار من الـ Select Component
	    $table->dateTime('schedule'); // التاريخ والوقت المختار بدقة عبر الـ DatePicker
	    $table->text('reason');
	    $table->text('note')->nullable();
	    $table->enum('status', ['pending', 'scheduled', 'cancelled'])->default('pending');
	    $table->text('cancellation_reason')->nullable();
	    $table->timestamps();
	});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
