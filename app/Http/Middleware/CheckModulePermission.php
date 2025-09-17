<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\SecRolePriv;

class CheckModulePermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $roleId = $user->role_id;

        $modulId = $request->route('id') ?? $request->modul_id;

        // Jika tidak pakai modul_id, izinkan (contoh: index)
        if (!$modulId && in_array($permission, [
            'role_id',
            'module_id',
            'allow_view',
            'allow_new',
            'allow_edit',
            'allow_delete',
            'allow_print',
            'allow_approve'
        ])) {
            return $next($request);
        }

        $query = SecRolePriv::where('role_id', $roleId);

        if ($modulId) {
            $query->where('module_id', $modulId);
        }

        $priv = $query->first();

        if (!$priv || !$priv->$permission) {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak: tidak punya izin ' . $permission
            ], 403);
        }

        return $next($request);
    }
}
