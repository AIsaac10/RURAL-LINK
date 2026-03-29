<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private function transformUser(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'location'      => $user->location,
            'description'   => $user->description,
            'profile_image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
        ];
    }

    private function saveUploadedImage($file, string $folder = 'profiles'): ?string
    {
        return $file ? $file->store($folder, 'public') : null;
    }

    private function saveBase64Image(?string $base64, string $folder = 'profiles'): ?string
    {
        if (!$base64) return null;
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $decoded = base64_decode($base64);
        if (!$decoded) return null;
        $filename = $folder . '/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($filename, $decoded);
        return $filename;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $imagePath = $request->hasFile('profile_image') 
            ? $this->saveUploadedImage($request->file('profile_image')) 
            : $this->saveBase64Image($request->input('profile_image_base64'));

        $user = User::create(array_merge($request->all(), [
            'password' => Hash::make($request->password),
            'profile_image' => $imagePath
        ]));

        return response()->json([
            'user' => $this->transformUser($user),
            'token' => $user->createToken('auth_token')->plainTextToken
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        return response()->json([
            'user' => $this->transformUser($user),
            'token' => $user->createToken('auth_token')->plainTextToken
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->only(['name', 'email', 'phone', 'location', 'description']);

        if ($request->has('password')) $data['password'] = Hash::make($request->password);

        if ($request->hasFile('profile_image') || $request->filled('profile_image_base64')) {
            if ($user->profile_image) Storage::disk('public')->delete($user->profile_image);
            $data['profile_image'] = $request->hasFile('profile_image') 
                ? $this->saveUploadedImage($request->file('profile_image')) 
                : $this->saveBase64Image($request->input('profile_image_base64'));
        }

        $user->update($data);
        return response()->json(['message' => 'Perfil atualizado!', 'user' => $this->transformUser($user)]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout ok!']);
    }
}