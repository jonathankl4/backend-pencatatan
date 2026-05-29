<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');          // snapshot nama (jaga-jaga produk dihapus)
            $table->decimal('cost_price', 12, 2);   // snapshot harga modal saat transaksi
            $table->decimal('sell_price', 12, 2);   // harga jual aktual (bisa beda dari default)
            $table->integer('quantity');
            $table->decimal('subtotal_cost', 12, 2);
            $table->decimal('subtotal_revenue', 12, 2);
            $table->decimal('subtotal_profit', 12, 2);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
