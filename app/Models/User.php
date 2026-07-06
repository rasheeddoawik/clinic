<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

// قمنا بتحديث الـ Fillable لإضافة حقل الـ phone المخصص للـ React وحذف الـ password
#[Fillable(['name', 'email', 'phone'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * علاقة المستخدم مع ملف المريض (One-to-One)
     */
    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    /**
     * علاقة المستخدم مع المواعيد (One-to-Many)
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}