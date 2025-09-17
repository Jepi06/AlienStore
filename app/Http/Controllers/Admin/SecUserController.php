<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SecUserController extends Controller
{
    public function index()
    {
        $users = SecUser::with('role')->get(); // asumsikan relasi 'role' didefinisikan di model
        return response()->json($users);
    }

    public function show($id)
    {
        $user = SecUser::with('role')->findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:4|unique:sec_user,name',
            'email' => 'required|email|unique:sec_user,email',
            'password' => 'required|min:6|confirmed',
            'passport' => 'nullable|string|max:255',
            'role_id' => 'required|integer|exists:sec_role,id',
        ], [
            'name.required' => 'Name wajib diisi.',
            'name.unique' => 'Name sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'role_id.required' => 'Role wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = SecUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'passport' => $request->passport,
            'role_id' => $request->role_id,
        ]);

        return response()->json([
            'message' => 'User berhasil dibuat.',
            'user' => $user->load('role')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = SecUser::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:4|unique:sec_user,name,' . $id,
            'email' => 'required|email|unique:sec_user,email,' . $id,
            'password' => 'nullable|min:6|confirmed',
            'passport' => 'nullable|string|max:255',
            'role_id' => 'required|integer|exists:sec_role,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->passport = $request->passport;
        $user->role_id = $request->role_id;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'user' => $user->load('role')
        ]);
    }

    public function destroy($id)
    {
        $user = SecUser::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus.'
        ]);
    }
}
