<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:12',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Buat pengguna baru
            $user = new User();
            $user->name = $validatedData['name'];
            $user->phone = $validatedData['phone'];
            $user->email = $validatedData['email'];
            $user->password = Hash::make($validatedData['password']); // Enkripsi password

            // Simpan foto avatar jika ada
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            }

            // Simpan pengguna ke database
            $user->save();

            // Response dengan token dan informasi pengguna
            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => new UserResource($user),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during registration: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            // Dapatkan pengguna yang sedang login
            $user = Auth::user();
            
            // Hapus semua token yang dimiliki pengguna ini
            $user->tokens()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Logout successful',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while logging out',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
