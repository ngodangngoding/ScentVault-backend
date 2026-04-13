<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $validate = $request->validate([
            'search' => 'nullable|string|max:255',
            'role' => 'nullable|in:admin,user',
            'per_page' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1'
        ]);

        $query = User::query()->latest();

        if (!empty($validate['search'])) {
            $query->where(function ($query) use ($validate) {
                $query->where('name', 'like', '%' . $validate['search'] . '%')
                    ->orWhere('email', 'like', '%' . $validate['search'] . '%');
            });
        }

        if (!empty($validate['role'])) {
            $query->where('role', $validate['role']);
        }

        $users = $query->paginate($validate['per_page'] ?? 15);

        return response()->json([
            'success' => true,
            'message' => 'User list successfully fetched',
            'data' => [
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total()
                ]
            ]
        ], 200);
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'role' => 'sometimes|in:admin,user',
            'region_code' => [
                'nullable',
                'string',
                Rule::exists('region', 'code')->where(
                    fn ($query) => $query->where('level', 4)
                )
            ]
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $validate['photo']->store('users', 'public');
        }

        $user = User::create([
            'name' => $validate['name'],
            'email' => $validate['email'],
            'password' => $validate['password'],
            'photo' => $photoPath,
            'role' => $validate['role'] ?? 'user',
            'region_code' => $validate['region_code'] ?? null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User successfully created',
            'data' => $this->userData($user)
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'message' => 'User detail successfully fetched',
            'data' => $this->userData($user)
        ], 200);
    }

    public function update(Request $request, User $user)
    {
        $validate = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'password' => 'sometimes|string|min:6|confirmed',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'region_code' => [
                'nullable',
                'string',
                Rule::exists('region', 'code')->where(
                    fn ($query) => $query->where('level', 4)
                )
            ]
        ]);

        $updateData = [];

        if (array_key_exists('name', $validate)) {
            $updateData['name'] = $validate['name'];
        }

        if (array_key_exists('email', $validate)) {
            $updateData['email'] = $validate['email'];
        }

        if (array_key_exists('password', $validate)) {
            $updateData['password'] = $validate['password'];
        }

        if (array_key_exists('region_code', $validate)) {
            $updateData['region_code'] = $validate['region_code'];
        }

        if ($request->hasFile('photo')) {
            $photoPath = $validate['photo']->store('users', 'public');

            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            $updateData['photo'] = $photoPath;
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User successfully updated',
            'data' => $this->userData($user)
        ], 200);
    }

    public function updateRole(Request $request, User $user)
    {
        $validate = $request->validate([
            'role' => 'required|in:admin,user'
        ]);

        $user->update([
            'role' => $validate['role']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User role successfully updated',
            'data' => $this->userData($user)
        ], 200);
    }

    public function destroy(User $user)
    {
        $deletedUserId = $user->id;

        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User successfully deleted',
            'data' => [
                'id' => $deletedUserId
            ]
        ], 200);
    }

    private function userData(User $user)
    {
        return $user->fresh()->load('region')->loadCount(['perfumes', 'scentLogs']);
    }
}
