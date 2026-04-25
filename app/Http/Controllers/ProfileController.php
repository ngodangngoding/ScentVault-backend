<?php

namespace App\Http\Controllers;

use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

#[Group('User - Profile', 'Endpoint untuk melihat dan mengelola profil user yang sedang login.', 3)]
class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('region.parent.parent.parent')->loadCount(['perfumes', 'scentLogs']);

        return response()->json([
            'success' => true,
            'message' => 'Profile successfully fetched',
            'data' => $user
        ], 200);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validate = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id)
            ]
        ]);

        $user->update($validate);

        return response()->json([
            'success' => true,
            'message' => 'Profile successfully updated',
            'data' => $this->profileData($user)
        ], 200);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validate = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed'
        ]);

        if (!Hash::check($validate['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'data' => null
            ], 422);
        }

        $user->update([
            'password' => $validate['password']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password successfully updated',
            'data' => $this->profileData($user)
        ], 200);
    }

    public function updateRegion(Request $request)
    {
        $user = $request->user();

        $validate = $request->validate([
            'region_code' => [
                'required',
                'string',
                Rule::exists('region', 'code')->where(
                    fn ($query) => $query->where('level', 4)
                ),
            ],
        ]);

        $user->update([
            'region_code' => $validate['region_code']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Region successfully updated',
            'data' => $this->profileData($user)
        ], 200);
    }

    public function updateAvatar(Request $request)
    {
        $user = $request->user();

        $validate = $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $photoPath = $validate['photo']->store('users', 'public');

        if ($user->photo && Storage::disk('public')->exists($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->update([
            'photo' => $photoPath
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile photo successfully updated',
            'data' => $this->profileData($user)
        ], 200);
    }

    private function profileData(User $user)
    {
        return $user->fresh()->load('region.parent.parent.parent')->loadCount(['perfumes', 'scentLogs']);
    }
}
