<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    /**
     * المرحلة الأولى: إنشاء مستخدم أساسي أو جلب بياناته إن كان مسجلاً مسبقاً
     */
    public function storeUser(Request $request): JsonResponse
    {
        // حذف شرط unique لكي لا ينهار الطلب عند إدخال مستخدم مسجل مسبقاً
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
        ]);

        try {
            // البحث عن المستخدم بالبريد الإلكتروني أو الهاتف لعدم التكرار
            $user = User::where('email', $validated['email'])
                        ->orWhere('phone', $validated['phone'])
                        ->first();

            if (!$user) {
                // إذا لم يكن موجوداً، قم بإنشائه كعضو جديد
                $user = User::create([
                    'name'  => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                ]);
                Log::info("New user created successfully with ID: {$user->id}");
                $statusCode = 201;
            } else {
                Log::info("Existing user logged in with ID: {$user->id}");
                $statusCode = 200;
            }
            
            return response()->json([
                'success' => true, 
                'id'      => $user->id,      
                'userId'  => $user->id,      
                'name'    => $user->name,
                'email'   => $user->email,
                'phone'   => $user->phone
            ], $statusCode);

        } catch (\Exception $e) {
            Log::error("Error storing/fetching user: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server Error'], 500);
        }
    }

    /**
     * جلب بيانات مستخدم محدد لعرضها في استمارة الفرونت آند
     */
    public function showUser($id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }

            return response()->json([
                'success' => true,
                'id'      => $user->id,
                'userId'  => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'phone'   => $user->phone
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error fetching user: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server Error'], 500);
        }
    }

    /**
     * المرحلة الثانية: حفظ الملف الطبي والوثيقة المرفوعة (Form 2)
     */
    public function registerPatient(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'                 => 'required|exists:users,id',
            'birth_date'              => 'required|date',
            'gender'                  => 'required|in:male,female,other',
            'address'                 => 'required|string',
            'occupation'              => 'required|string',
            'emergency_contact_name'  => 'required|string',
            'emergency_contact_number'=> 'required|string',
            'insurance_provider'      => 'nullable|string',
            'insurance_policy_number' => 'nullable|string',
            'allergies'               => 'nullable|string',
            'current_medication'      => 'nullable|string',
            'family_medical_history'  => 'nullable|string',
            'past_medical_history'    => 'nullable|string',
            'identification_type'     => 'required|string',
            'identification_number'   => 'required|string',
            'identificationDocument'  => 'nullable|file|mimes:jpg,jpeg,png,svg,pdf|max:5120',
            'treatment_consent'       => 'required|boolean',
            'disclosure_consent'      => 'required|boolean',
            'privacy_consent'         => 'required|boolean',
        ]);

        try {
            DB::beginTransaction();
            $data = $validated;

            if ($request->hasFile('identificationDocument')) {
                $file = $request->file('identificationDocument');
                $path = $file->store('patients/documents', 'public');
                $data['identification_document_path'] = $path;
            }

            unset($data['identificationDocument']);
            $patient = Patient::create($data);
            DB::commit();

            return response()->json([
                'success'   => true, 
                'id'        => $patient->id,
                'patientId' => $patient->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error registering patient: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Registration Failed.'], 500);
        }
    }

    /**
     * جلب بيانات الملف الطبي للمريض متوافقة تماماً مع Next.js
     */
    public function getPatient($userId): JsonResponse
    {
        try {
            $patient = Patient::where('user_id', $userId)->first();

            if (!$patient) {
                return response()->json(['message' => 'Patient record not found'], 404);
            }

            return response()->json([
                'id'                     => $patient->id,
                '$id'                    => (string)$patient->id, 
                'userId'                 => $patient->user_id,
                'user_id'                => $patient->user_id,
                'name'                   => $patient->name,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error fetching patient: " . $e->getMessage());
            return response()->json(['message' => 'Server Error'], 500);
        }
    }
}