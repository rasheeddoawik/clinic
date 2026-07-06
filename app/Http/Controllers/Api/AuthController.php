<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * التحقق من الـ Passkey الـ 6 أرقام الخاص بالمسؤول عند دخول لوحة التحكم
     */
    public function verifyAdminPasskey(Request $request): JsonResponse
    {
        $request->validate(['passkey' => 'required|string|size:6']);

        // فحص آمن على مستوى السيرفر بدلاً من الفحص المكشوف بالمتصفح
        if ($request->passkey === env('ADMIN_PASSKEY', '111111')) {
            return response()->json(['authenticated' => true], 200);
        }

        return response()->json(['authenticated' => false, 'message' => 'Invalid passkey.'], 401);
    }
}