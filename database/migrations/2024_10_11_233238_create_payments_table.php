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
        $table->foreignId('commande_id')->constrained('commandes')->onDelete('cascade'); // Lien avec la table des commandes
        $table->string('payment_method'); // Méthode de paiement (ex: PayPal, Paiement à la livraison)
        $table->decimal('amount', 10, 2); // Montant du paiement
        $table->string('currency')->default('USD'); // Devise utilisée pour le paiement
        $table->string('payment_status'); // Statut du paiement (ex: 'completed', 'pending', 'failed')
        $table->string('transaction_id')->nullable(); // ID de transaction si applicable
            $table->timestamps();
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
