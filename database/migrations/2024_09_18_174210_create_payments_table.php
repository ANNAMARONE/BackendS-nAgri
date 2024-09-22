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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->decimal('amount', 8, 2);
            $table->string('currency');
            $table->string('status');
            $table->string('customer_email');

            $table->string('operator_id')->nullable();
            $table->string('operator')->nullable();
            $table->decimal('paid_amount', 8, 2)->nullable();
            $table->string('paid_currency', 3)->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payements');
    }
};
