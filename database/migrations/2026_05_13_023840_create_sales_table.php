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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('sale_code')->unique();   // SL-20260101-001
            $table->decimal('total_cost', 12, 2);
            $table->decimal('total_revenue', 12, 2);
            $table->decimal('gross_profit', 12, 2); // total_revenue - total_cost
            $table->text('notes')->nullable();
            $table->date('sale_date');
            $table->timestamps();
            $table->index(['user_id', 'sale_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
