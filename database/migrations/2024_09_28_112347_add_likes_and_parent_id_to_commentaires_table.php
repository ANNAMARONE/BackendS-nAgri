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
        Schema::table('commentaires', function (Blueprint $table) {
            $table->unsignedInteger('likes')->default(0); 
            $table->foreignId('parent_id')->nullable()->constrained('commentaires')->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commentaires', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['likes', 'parent_id']); 
        });
    }
};
