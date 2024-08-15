<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            // Ambil semua data pengguna
            $users = User::all();

            // Menggunakan UserResource untuk memformat data
            return response()->json([
                'status' => 'success',
                'message' => 'Users retrieved successfully',
                'data' => UserResource::collection($users) // Mengembalikan koleksi UserResource
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving users: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:12',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Simpan foto ke storage jika ada
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            // Buat data pengguna baru
            $user = new User();
            $user->name = $request->name;
            $user->phone = $request->phone; // Menyimpan data phone
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->avatar = $avatarPath; // Path gambar
            $user->save();


            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => new UserResource($user),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:12',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'required|string|min:6',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Temukan pengguna berdasarkan ID
            $user = User::findOrFail($id);

            // if ($user->user_id !== Auth::id()) {
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => 'Unauthorized to update this task',
            //     ], 403);
            // }

            // Update kolom dengan nilai yang diberikan dalam request
            $user->name = $validated['name'];
            $user->phone = $validated['phone'];
            $user->email = $validated['email'];
            $user->password = Hash::make($validated['password']); // Enkripsi password

            if ($request->hasFile('avatar')) {
                // Simpan foto ke storage
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            }

            // Simpan perubahan
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => new UserResource($user)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the user: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Temukan pengguna berdasarkan ID
            $user = User::findOrFail($id);

            // Hapus pengguna dari database
            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting the user: ' . $e->getMessage(),
            ], 500);
        }
    }

}
