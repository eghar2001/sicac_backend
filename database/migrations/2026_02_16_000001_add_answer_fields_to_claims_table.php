<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->text('answer')->nullable()->after('description');
            $table->timestamp('answered_at')->nullable()->after('answer');
            $table->enum('status', ['pending', 'completed', 'cancelled', 'answered'])
                ->default('pending')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->enum('status', ['pending', 'completed', 'cancelled'])
                ->default('pending')
                ->change();
            $table->dropColumn(['answer', 'answered_at']);
        });
    }
};
