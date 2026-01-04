<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'تم حذف المستخدم بنجاح']);
    }

    public function changeRole(Request $request, $id)
    {
        // تحقق من الدور
        $request->validate([
            'role' => 'required|in:admin,user',
        ]);

        $user = User::findOrFail($id);

        // مسح الأدوار القديمة
        $user->syncRoles([]);

        // إنشاء الدور لو مش موجود
        Role::firstOrCreate(['name' => $request->role, 'guard_name' => 'web']);

        // تخصيص الدور
        $user->assignRole($request->role);
        // إنشاء المتجر أو الخدمة إذا لزم الأمر
        return response()->json(['user' => new UserResource($user)]);
    }

    public function count()
    {
        return response()->json([
            'count' => User::count()
        ]);
    }

    public function index()
    {
        $users = User::paginate(6);
        return response()->json([
        'users' => UserResource::collection($users),
        'current_page' => $users->currentPage(),
        'last_page' => $users->lastPage(),
        'total' => $users->total(),

    ]);
    }
}
