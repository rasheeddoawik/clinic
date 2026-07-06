<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;

    /**
     * ميزة لارافل 13: جعل جميع الحقول قابلة للملء دفعة واحدة بأمان 
     * لأن استمارة المريض تحتوي على حقول طبية وشخصية كثيرة قادمة من الـ React
     */
    protected $guarded = [];

    /**
     * علاقة المريض مع حساب المستخدم الأساسي (كل ملف مريض يتبع لمستخدم واحد)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}