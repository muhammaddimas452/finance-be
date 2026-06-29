<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // 1. AMBIL SEMUA TRANSAKSI
    public function index(Request $request)
    {
        // Eager load wallet dan category agar nama dompet & kategori langsung terbaca di JSON
        $transactions = $request->user()->transactions()->with(['wallet', 'category'])->latest('date')->get();
        return response()->json($transactions);
    }

    // 2. SIMPAN TRANSAKSI & UPDATE SALDO
    public function store(Request $request)
    {
        $validated = $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'category_id' => 'nullable|exists:categories,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:income,expense',
            'date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $wallet = Wallet::where('id', $request->wallet_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Gunakan Database Transaction agar aman
        DB::beginTransaction();
        try {
            // Buat transaksi
            $transaction = $request->user()->transactions()->create($validated);

            // Update saldo dompet
            if ($validated['type'] === 'income') {
                $wallet->increment('balance', $validated['amount']);
            } elseif ($validated['type'] === 'expense') {
                $wallet->decrement('balance', $validated['amount']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaksi berhasil dicatat',
                'transaction' => $transaction->load(['wallet', 'category'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mencatat transaksi', 'error' => $e->getMessage()], 500);
        }
    }

    // 3. HAPUS TRANSAKSI & KEMBALIKAN SALDO
    public function destroy(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        DB::beginTransaction();
        try {
            $wallet = $transaction->wallet;

            // Kembalikan saldo seperti semula sebelum dihapus
            if ($transaction->type === 'income') {
                $wallet->decrement('balance', $transaction->amount);
            } elseif ($transaction->type === 'expense') {
                $wallet->increment('balance', $transaction->amount);
            }

            $transaction->delete();
            DB::commit();

            return response()->json(['message' => 'Transaksi dihapus, saldo dikembalikan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus transaksi'], 500);
        }
    }
}
