<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            // Admin hanya boleh membuat ADMIN atau LIBRARIAN
            'role' => ['required', Rule::in(['ADMIN', 'LIBRARIAN'])],
        ]);

        $user = User::create($data + ['is_active' => true]);

        return response()->json(['data' => $user], 201);
    }

    public function resetPassword(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::findOrFail($id);
        $user->update(['password' => $data['password']]);

        // Paksa user tersebut login ulang (kecuali admin yang sedang reset akunnya sendiri)
        if ($user->id !== $request->user()->id) {
            $user->tokens()->delete();
        }

        return response()->json(['message' => 'Password berhasil direset']);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user = User::findOrFail($id);
        $isActive = $request->boolean('is_active');

        if (! $isActive && $user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Tidak dapat menonaktifkan akun sendiri',
            ], 422);
        }

        $user->update(['is_active' => $isActive]);

        // User nonaktif langsung kehilangan semua sesi
        if (! $isActive) {
            $user->tokens()->delete();
        }

        return response()->json(['data' => $user]);
    }
}
