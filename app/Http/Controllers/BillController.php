<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;

class BillController extends Controller
{
    // Mengambil semua data tagihan milik user yang login
    public function index(Request $request)
    {
        $bills = Bill::where('user_id', $request->user()->id)->get();

        $currentMonthYear = now()->format('Y-m'); // Contoh hasil: "2026-07"

        foreach ($bills as $bill) {
            // Cek apakah tagihan lunas, TAPI dibayarnya di bulan sebelumnya
            if ($bill->is_paid && $bill->updated_at->format('Y-m') < $currentMonthYear) {
                $bill->is_paid = false;
                $bill->save(); // Otomatis mengupdate updated_at ke bulan sekarang
            }
        }

        // Jangan lupa di-Push ke GitHub agar Railway memperbarui API-nya!
        return response()->json(['success' => true, 'data' => $bills]);
    }

    // Menambah tagihan baru
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|integer|min:1|max:31',
            'icon' => 'nullable|string',
        ]);

        $bill = Bill::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'amount' => $request->amount,
            'due_date' => $request->due_date,
            'icon' => $request->icon ?? 'FileText',
            'is_paid' => false,
        ]);

        return response()->json(['success' => true, 'message' => 'Tagihan berhasil ditambahkan', 'data' => $bill]);
    }

    // Mengubah data tagihan
    public function update(Request $request, $id)
    {
        $bill = Bill::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
            'due_date' => 'sometimes|required|integer|min:1|max:31',
            'icon' => 'nullable|string',
            'is_paid' => 'boolean'
        ]);

        $bill->update($request->all());

        return response()->json(['success' => true, 'message' => 'Tagihan berhasil diupdate', 'data' => $bill]);
    }

    // Menghapus tagihan
    public function destroy(Request $request, $id)
    {
        $bill = Bill::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        $bill->delete();

        return response()->json(['success' => true, 'message' => 'Tagihan berhasil dihapus']);
    }
}
