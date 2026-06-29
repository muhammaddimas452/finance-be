<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validasi input dari Frontend
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // 2. Buat User baru ke Database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Password wajib di-hash!
            // Kita generate avatar default berdasarkan nama menggunakan UI Avatars
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($request->name) . '&background=random',
        ]);

        // 3. Buatkan API Token untuk user ini
        $token = $user->createToken('auth_token')->plainTextToken;

        // 4. Kembalikan balikan (response) format JSON ke React
        return response()->json([
            'message' => 'Registrasi berhasil',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // --- FITUR LOGIN ---
    public function login(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // 2. Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // 3. Cek apakah user ada dan passwordnya cocok
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        // 4. Jika benar, buatkan Token baru
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
        ]);
    }

    // --- FITUR LOGOUT ---
    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }
    public function updateProfile(Request $request)
    {
        // 1. Ambil data user yang sedang login
        $user = $request->user();

        // 2. Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // 3. Update nama
        $user->name = $request->name;

        // 4. Update password jika diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // 5. Update avatar jika ada file yang diunggah
        if ($request->hasFile('avatar')) {
            // Hapus avatar lama dari storage jika bukan foto default
            if ($user->avatar) {
                // Ekstrak nama file dari URL lengkap untuk dihapus
                $oldPath = str_replace(url('storage') . '/', '', $user->avatar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Simpan foto baru ke folder storage/app/public/avatars
            $path = $request->file('avatar')->store('avatars', 'public');

            // Simpan URL lengkap ke database agar frontend bisa langsung me-render gambarnya
            $user->avatar = url('storage/' . $path);
        }

        // 6. Simpan perubahan ke database
        $user->save();

        // 7. Kembalikan respons sukses beserta data user terbaru
        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'user' => $user
        ]);
    }
}
