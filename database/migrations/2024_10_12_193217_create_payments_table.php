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
            $table->id(); // ID de paiement
            $table->string('transaction_id')->unique(); // ID de la transaction (PayDunya)
            $table->string('customer_name'); // Nom du client
            $table->string('customer_email'); // Email du client
            $table->string('customer_phone')->nullable(); // Téléphone du client
            $table->decimal('amount', 10, 2); // Montant du paiement
            $table->string('currency')->default('XOF'); // Devise
            $table->string('status'); // Statut (completed, pending, cancelled)
            $table->text('receipt_url')->nullable(); // URL du reçu PDF
            $table->text('custom_data')->nullable(); // Données personnalisées
            $table->timestamps(); // Colonnes created_at et updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
