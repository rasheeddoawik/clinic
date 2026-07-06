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
        Schema::create('patients', function (Blueprint $table) {
   	 $table->id(); // يمثل الـ patientId في الـ React
   	 $table->foreignId('user_id')->constrained()->onDelete('cascade');
    
	    // البيانات الشخصية والديموغرافية
	    $table->date('birth_date');
	    $table->enum('gender', ['male', 'female', 'other']);
	    $table->string('address');
	    $table->string('occupation');
	    $table->string('emergency_contact_name');
	    $table->string('emergency_contact_number', 20);
    
	    // المعلومات الطبية والتأمين
	    $table->string('insurance_provider')->nullable();
	    $table->string('insurance_policy_number')->nullable();
	    $table->text('allergies')->nullable();
	    $table->text('current_medication')->nullable();
	    $table->text('family_medical_history')->nullable();
	    $table->text('past_medical_history')->nullable();
    
	    // الوثائق والمستندات المرفوعة
	    $table->string('identification_type');
	    $table->string('identification_number');
	    $table->string('identification_document_path')->nullable(); // مسار الملف المرفوع من FileUploader
    
	    // الإقرارات والموافقات (Checkboxes)
	    $table->boolean('treatment_consent')->default(false);
	    $table->boolean('disclosure_consent')->default(false);
	    $table->boolean('privacy_consent')->default(false);
    
	    $table->timestamps();
	});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
