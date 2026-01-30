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
        Schema::create('technician_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requesting_user_id')->constrained('users');
            $table->foreignId('technician_id')->nullable()->constrained('users');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('subject');
            $table->text('description');
            $table->date('wanted_date_start');
            $table->date('wanted_date_end');
            $table->string('time_shift');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('technician_requests');
    }
};
