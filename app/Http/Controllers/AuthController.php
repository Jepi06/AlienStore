<?php

namespace App\Http\Controllers;

use App\Models\SecUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function Register(Request $request)
    {
        // membuat validasi
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:sec_user',
            'name' => 'required|min:4|unique:sec_user',
            'password' => 'required|min:6|confirmed',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',

            'name.required' => 'Name wajib diisi.',
            'name.min' => 'Name minimal 4 karakter.',
            'name.unique' => 'Name sudah digunakan.',

            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',

        ]);
        // check apakah validasi berhasil atau tidak
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        // mengirim data ke database sesuai dengan request
        $user = SecUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id,
        ]);
        // jika berhasil
        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user' => $user
        ], 201);
    }

    public function Login(Request $request)
    {
        // validasi
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);
        // check validasi
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        // ambil semua user dengan role(function di model) yang emailnya = email di request
        $user = SecUser::with('role')->where('email', $request->email)->first();
        // check emailnya ada atau tidak
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'email tidak terdaftar'
            ], 404);
        }
        // check passwordnya sama atau tidak
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'password salah'
            ], 401);
        }
        // membuat token yang mengandung email dan password
        $credentials = $request->only('email', 'password');
        $token = auth()->guard('api')->attempt($credentials);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'gagal login'
            ], 401);
        }
        //respon jsonnya
        return response()->json([
            'success' => true,
            'message' => 'berhasil login',
            'user' => auth()->guard('api')->user(),
            'token' => $token,
            'role' => $user->role ? $user->role->name : null, // ✅ FIXED
        ]);
    }
}
