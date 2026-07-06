<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CarePulseTestSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@carepulse.com',
            'phone' => '+970599112233',
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Main Street, Gaza',
            'occupation' => 'Designer',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_number' => '+970599445566',
            'identification_type' => 'Driver License',
            'identification_number' => 'DL-99823',
            'treatment_consent' => true,
            'disclosure_consent' => true,
            'privacy_consent' => true,
        ]);

        // موعد مجدول (Scheduled)
        Appointment::create([
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'primary_physician' => 'John Green',
            'schedule' => Carbon::now()->addDays(2),
            'reason' => 'Checkup',
            'status' => 'scheduled',
        ]);

        // موعد قيد الانتظار (Pending)
        Appointment::create([
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'primary_physician' => 'Leila Cameron',
            'schedule' => Carbon::now()->addDays(4),
            'reason' => 'Consultation',
            'status' => 'pending',
        ]);
    }
}