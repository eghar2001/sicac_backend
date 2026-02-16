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
        Schema::table('technician_requests', function (Blueprint $table) {
            $table->foreignId('claim_id')->nullable()->after('category_id')->constrained('claims')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technician_requests', function (Blueprint $table) {
            $table->dropForeign(['claim_id']);
            $table->dropColumn('claim_id');
        });
    }
};
