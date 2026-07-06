<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    // فتح كافة الحقول للحفظ المباشر لتسريع عملية الربط مع استمارة الـ React
    protected $guarded = [];

    /**
     * ضبط تحويل أنواع البيانات (Casting):
     * نقوم بتحويل حقل الـ schedule إلى كائن DateTime تلقائياً ليتوافق مع الـ DatePicker والـ ISO String في الـ React
     */
    protected function casts(): array
    {
        return [
            'schedule' => 'datetime',
        ];
    }

    /**
     * علاقة الموعد مع المستخدم (صاحب الموعد)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * علاقة الموعد مع الملف الطبي للمريض
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}