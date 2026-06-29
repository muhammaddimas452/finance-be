<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;

class WalletController extends Controller
{
    // 1. TAMPILKAN SEMUA DOMPET (Milik User yang Login)
    public function index(Request $request)
    {
        // Mengambil dompet menggunakan relasi Eloquent yang kita buat sebelumnya
        $wallets = $request->user()->wallets()->get();
        return response()->json($wallets);
    }

    // 2. BUAT DOMPET BARU
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'balance' => 'numeric',
            'icon' => 'string',
            'is_primary' => 'boolean'
        ]);

        $wallet = $request->user()->wallets()->create([
            'name' => $request->name,
            'balance' => $request->balance ?? 0,
            'icon' => $request->icon ?? 'Wallet',
            'is_primary' => $request->is_primary ?? false,
        ]);

        return response()->json([
            'message' => 'Dompet berhasil dibuat',
            'wallet' => $wallet
        ], 201);
    }

    // 3. UPDATE DOMPET (Misal: ganti nama atau update saldo manual)
    public function update(Request $request, Wallet $wallet)
    {
        // KEAMANAN: Pastikan dompet ini benar-benar milik user yang merequest
        if ($wallet->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'balance' => 'numeric',
            'icon' => 'string',
            'is_primary' => 'boolean'
        ]);

        $wallet->update($validated);

        return response()->json([
            'message' => 'Dompet berhasil diperbarui',
            'wallet' => $wallet
        ]);
    }

    // 4. HAPUS DOMPET
    public function destroy(Request $request, Wallet $wallet)
    {
        // KEAMANAN: Pastikan dompet ini milik user yang merequest
        if ($wallet->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $wallet->delete();

        return response()->json(['message' => 'Dompet berhasil dihapus']);
    }

    // --- FITUR TRANSFER ANTAR DOMPET ---
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'from_wallet_id' => 'required|exists:wallets,id',
            'to_wallet_id' => 'required|exists:wallets,id|different:from_wallet_id',
            'amount' => 'required|numeric|min:1',
        ]);

        // Pastikan kedua dompet adalah milik user yang login
        $fromWallet = $request->user()->wallets()->findOrFail($validated['from_wallet_id']);
        $toWallet = $request->user()->wallets()->findOrFail($validated['to_wallet_id']);

        // Gunakan DB Transaction agar jika terjadi error, uang tidak hilang
        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 1. Pindahkan saldo
            $fromWallet->decrement('balance', $validated['amount']);
            $toWallet->increment('balance', $validated['amount']);

            // 2. Catat di histori transaksi (menggunakan dompet asal)
            $transaction = $request->user()->transactions()->create([
                'wallet_id' => $fromWallet->id,
                'title' => 'Transfer ke ' . $toWallet->name,
                'amount' => $validated['amount'],
                'type' => 'transfer',
                'date' => now()->toDateString(),
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'message' => 'Transfer berhasil',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['message' => 'Transfer gagal', 'error' => $e->getMessage()], 500);
        }
    }
}
