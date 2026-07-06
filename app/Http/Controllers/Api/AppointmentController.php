<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * 1. دالة إنشاء وحفظ الموعد الجديد
     */
    public function store(Request $request): JsonResponse
    {
        try {
            Log::info("Data received from React for new appointment: ", $request->all());

            if ($request->has('schedule') && !empty($request->schedule)) {
                try {
                    $request->merge([
                        'schedule' => Carbon::parse($request->schedule)->toDateTimeString()
                    ]);
                } catch (\Exception $dateException) {
                    Log::error("Failed to parse date string: " . $dateException->getMessage());
                }
            }

            $validatedData = $request->validate([
                'userId'           => 'required|string',
                'patientId'        => 'required|string',
                'schedule'         => 'required',
                'reason'           => 'nullable|string',
                'note'             => 'nullable|string',
                'primaryPhysician' => 'required|string',
                'status'           => 'nullable|string'
            ]);

            $appointment = Appointment::create([
                'user_id'           => $validatedData['userId'],
                'patient_id'        => $validatedData['patientId'],
                'schedule'          => $validatedData['schedule'],
                'reason'            => $validatedData['reason'] ?? '',
                'note'              => $validatedData['note'] ?? '',
                'primary_physician' => $validatedData['primaryPhysician'],
                'status'            => $validatedData['status'] ?? 'pending',
            ]);

            return response()->json([
                'success' => true,
                '$id'     => (string)$appointment->id,
                'id'      => $appointment->id,
                'userId'  => $appointment->user_id,
                'status'  => $appointment->status,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("Validation failed during appointment creation: ", $e->errors());
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Database or general error creating appointment: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 2. دالة لوحة التحكم الإدارية (Admin Dashboard)
     */
    public function adminDashboard()
    {
        try {
            $appointments = Appointment::with('user')->orderBy('schedule', 'desc')->get();

            $scheduledCount = $appointments->where('status', 'scheduled')->count();
            $pendingCount   = $appointments->where('status', 'pending')->count();
            $cancelledCount = $appointments->where('status', 'cancelled')->count();

            $formattedDocuments = [];
            
            foreach ($appointments as $appointment) {
                $formattedDocuments[] = [
                    '$id' => (string)$appointment->id,
                    'userId' => (string)$appointment->user_id,
                    'patient' => [
                        '$id' => (string)$appointment->patient_id,
                        'name' => $appointment->user->name ?? 'Unknown Patient',
                    ],
                    'status' => $appointment->status,
                    'schedule' => $appointment->schedule ? Carbon::parse($appointment->schedule)->toIso8601String() : now()->toIso8601String(),
                    'primaryPhysician' => $appointment->primary_physician,
                    'reason' => $appointment->reason,
                    'note' => $appointment->note,
                    'cancellationReason' => $appointment->cancellation_reason,
                ];
            }

            return response()->json([
                'documents' => $formattedDocuments,
                'scheduledCount' => $scheduledCount,
                'pendingCount' => $pendingCount,
                'cancelledCount' => $cancelledCount,
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error in adminDashboard controller: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب البيانات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. دالة جلب تفاصيل موعد محدد
     */
    public function show($id): JsonResponse
    {
        try {
            $appointment = Appointment::find($id);

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'الموعد غير موجود في لارافل'
                ], 404);
            }

            $formattedAppointment = [
                '$id'               => (string)$appointment->id,
                'id'                => $appointment->id,
                'userId'            => (string)$appointment->user_id,
                'patientId'         => (string)$appointment->patient_id,
                'primaryPhysician'  => $appointment->primary_physician,
                'schedule'          => $appointment->schedule,
                'status'            => $appointment->status,
                'reason'            => $appointment->reason,
                'note'              => $appointment->note,
                'cancellationReason'=> $appointment->cancellation_reason,
            ];

            return response()->json($formattedAppointment, 200);

        } catch (\Exception $e) {
            Log::error("Error in show appointment controller: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في السيرفر أثناء جلب تفاصيل الموعد'
            ], 500);
        }
    }

    /**
     * 4. دالة تحديث حالة الموعد (جدولة/تأكيد أو إلغاء) من لوحة الآدمين
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            Log::info("Update data received for appointment ID {$id}: ", $request->all());

            $appointment = Appointment::find($id);

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'الموعد غير موجود في قاعدة البيانات'
                ], 404);
            }

            $inputData = $request->all();

            $schedule = $appointment->schedule;
            if (isset($inputData['schedule']) && !empty($inputData['schedule'])) {
                try {
                    $schedule = Carbon::parse($inputData['schedule'])->toDateTimeString();
                } catch (\Exception $e) {
                    Log::error("Failed to parse update date: " . $e->getMessage());
                }
            }

            $appointment->update([
                'primary_physician'   => $inputData['primaryPhysician'] ?? $appointment->primary_physician,
                'schedule'            => $schedule,
                'status'              => $inputData['status'] ?? $appointment->status,
                'cancellation_reason' => $inputData['cancellationReason'] ?? $appointment->cancellation_reason,
            ]);

            return response()->json([
                'success' => true,
                'id'      => $appointment->id,
                '$id'     => (string)$appointment->id,
                'status'  => $appointment->status,
                'primaryPhysician'   => $appointment->primary_physician,
                'schedule'            => $appointment->schedule,
                'cancellationReason' => $appointment->cancellation_reason,
            ], 200);

        } catch (\Exception $e) {
            Log::error("Error updating appointment: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في السيرفر أثناء تحديث الموعد',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}