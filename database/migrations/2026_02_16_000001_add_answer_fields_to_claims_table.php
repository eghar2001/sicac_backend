<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "claims" DROP CONSTRAINT IF EXISTS "claims_status_check"');
            DB::statement(
                'ALTER TABLE "claims" ADD CONSTRAINT "claims_status_check" ' .
                'CHECK ("status" IN (\'pending\', \'completed\', \'cancelled\', \'answered\'))'
            );
            DB::statement('ALTER TABLE "claims" ALTER COLUMN "status" SET DEFAULT \'pending\'');

            return;
        }

        Schema::table('claims', function (Blueprint $table) {
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
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            // Make existing rows compatible with the previous check constraint.
            DB::statement('UPDATE "claims" SET "status" = \'completed\' WHERE "status" = \'answered\'');
            DB::statement('ALTER TABLE "claims" DROP CONSTRAINT IF EXISTS "claims_status_check"');
            DB::statement(
                'ALTER TABLE "claims" ADD CONSTRAINT "claims_status_check" ' .
                'CHECK ("status" IN (\'pending\', \'completed\', \'cancelled\'))'
            );
            DB::statement('ALTER TABLE "claims" ALTER COLUMN "status" SET DEFAULT \'pending\'');
        } else {
            Schema::table('claims', function (Blueprint $table) {
                $table->enum('status', ['pending', 'completed', 'cancelled'])
                    ->default('pending')
                    ->change();
            });
        }

        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['answer', 'answered_at']);
        });
    }
};
