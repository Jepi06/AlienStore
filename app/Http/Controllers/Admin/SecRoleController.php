<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecRole;
use Illuminate\Http\Request;

class SecRoleController extends Controller
{
    public function index()
    {
        $roles = SecRole::all();
        return response()->json($roles);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:20',
            'description' => 'nullable|string|max:100',
            'active' => 'nullable|boolean',
        ]);

        $role = SecRole::create([
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->active ?? 1
        ]);

        return response()->json([
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    public function show($id)
    {
        $role = SecRole::findOrFail($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:20',
            'description' => 'nullable|string|max:100',
            'active' => 'nullable|boolean',
        ]);

        $role = SecRole::findOrFail($id);
        $role->update($request->only(['name', 'description', 'active']));

        return response()->json([
            'message' => 'Role updated successfully',
            'data' => $role
        ]);
    }

    public function destroy($id)
    {
        $role = SecRole::findOrFail($id);
        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }
}
