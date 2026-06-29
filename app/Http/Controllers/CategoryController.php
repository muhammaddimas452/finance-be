<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Tampilkan semua kategori milik user
    public function index(Request $request)
    {
        return response()->json($request->user()->categories);
    }

    // Buat kategori baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'icon' => 'nullable|string'
        ]);

        $category = $request->user()->categories()->create($validated);

        return response()->json([
            'message' => 'Kategori berhasil dibuat',
            'category' => $category
        ], 201);
    }

    // Update kategori
    public function update(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'type' => 'in:income,expense',
            'icon' => 'nullable|string'
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Kategori diperbarui',
            'category' => $category
        ]);
    }

    // Hapus kategori
    public function destroy(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $category->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}
