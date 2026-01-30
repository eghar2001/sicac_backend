<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->string('name');
            $table->foreignId('brand_id')->constrained();
            $table->foreignId('subfamily_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->string('model_sku')->nullable();
            $table->decimal('price_ars', 14, 2)->nullable();
            $table->longText('description')->nullable();
            $table->jsonb('technical_specs')->nullable();

            $table->timestamps();

            $table->index(['brand_id', 'subfamily_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
