<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title'); // Contoh: "Langganan Netflix"
            $table->decimal('amount', 15, 2); // Jumlah tagihan
            $table->integer('due_date'); // Tanggal jatuh tempo setiap bulannya (angka 1-31)
            $table->string('icon')->default('FileText'); // Ikon tagihan
            $table->boolean('is_paid')->default(false); // Status bulan ini sudah dibayar atau belum
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
