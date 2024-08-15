<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // Mendapatkan semua tugas
    public function index()
    {
        try {
            $tasks = Task::all();

            return response()->json([
                'status' => 'success',
                'message' => 'Tasks retrieved successfully',
                'data' => TaskResource::collection($tasks)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving tasks: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Menampilkan detail tugas berdasarkan ID
    public function show($id)
    {
        try {
            $task = Task::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Task retrieved successfully',
                'data' => new TaskResource($task)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving the task: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Menyimpan tugas baru
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|string|max:50',
                'due_date' => 'required|date',
            ]);

            // Buat tugas baru
            $task = Task::create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Task created successfully',
                'data' => new TaskResource($task),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while creating the task: ' . $e->getMessage(),
            ], 500);
        }
    }

    

    public function update(Request $request, $id)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|string|max:50',
                'due_date' => 'required|date',
            ]);

            // Temukan tugas berdasarkan ID
            $task = Task::findOrFail($id);

            // Periksa apakah pengguna yang sedang login adalah pemilik tugas
            if ($task->user_id !== Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to update this task',
                ], 403);
            }

            // Update tugas
            $task->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Task updated successfully',
                'data' => new TaskResource($task),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the task: ' . $e->getMessage(),
            ], 500);
        }
    }


    // Menghapus tugas
    public function destroy($id)
    {
        try {
            // Temukan tugas berdasarkan ID
            $task = Task::findOrFail($id);

            if ($task->user_id !== Auth::id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to delete this task',
                ], 403);
            }

            // Hapus tugas
            $task->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Task deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting the task: ' . $e->getMessage(),
            ], 500);
        }
    }
}
