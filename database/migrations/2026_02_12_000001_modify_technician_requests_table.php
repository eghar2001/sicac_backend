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
            // Modificar la constraint de FK 'technician_id' de 'users' a 'technicians'
            $table->dropForeign(['technician_id']);
            $table->foreign('technician_id')->references('id')->on('technicians')->nullOnDelete();
            $table->unsignedBigInteger('category_id')->nullable()->change();
            
            // campos nuevo de Tipo servicio y estado
            $table->enum('type', ['technical_service', 'claim'])->default('technical_service')->after('category_id');
            $table->enum('status', ['pending', 'assigned', 'completed', 'cancelled'])->default('pending')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technician_requests', function (Blueprint $table) {
            // Revertir la constraint
            $table->dropForeign(['technician_id']);
            $table->foreign('technician_id')->references('id')->on('users')->nullOnDelete();
            
            // Eliminar los campos enum
            $table->dropColumn(['type', 'status']);
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
        });
    }
};
