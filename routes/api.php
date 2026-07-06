<?php

use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan; // 👈 الاستدعاء الصحيح والوحيد هنا

/*
|--------------------------------------------------------------------------
| API Routes - CarePulse Backend (Laravel 13)
|--------------------------------------------------------------------------
*/

// 1. مسارات استمارات المرضى والتسجيل الأساسي
Route::post('/users', [PatientController::class, 'storeUser']);
Route::get('/users/{id}', [PatientController::class, 'showUser']); 
Route::post('/patients/register', [PatientController::class, 'registerPatient']);
Route::get('/patients/{userId}', [PatientController::class, 'getPatient']);

// 2. مسارات جدولة المواعيد وإلغائها وتحديث الحالات عبر المودال
Route::post('/appointments', [AppointmentController::class, 'store']);
Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
Route::put('/appointments/{id}', [AppointmentController::class, 'update']); 

// 3. مسارات لوحة التحكم والتحقق الآمن من الـ Passkey للمسؤول (Admin)
Route::post('/admin/verify-passkey', [AuthController::class, 'verifyAdminPasskey']);
Route::get('/admin/dashboard', [AppointmentController::class, 'adminDashboard']);

////////////////////////////

// مسار تشغيل الهجرة الآمنة
Route::get('/run-migration-securely', function () {
    try {
        // مسح الكاش أولاً للتأكد من قراءة كل شيء بنظافة
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        
        // تشغيل الـ Migration لإصلاح وبناء الجداول
        Artisan::call('migrate', ['--force' => true]);
        return response()->json(['message' => 'قاعدة البيانات تم تهجيرها بنجاح واكتمل إنشاء الجداول! 🎉']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});