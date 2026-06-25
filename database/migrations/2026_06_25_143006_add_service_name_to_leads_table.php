<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('service_name')->nullable()->after('service_id');
        });

        // Backfill service_name from existing service relationship
        DB::statement('
            UPDATE leads
            JOIN services ON services.id = leads.service_id
            SET leads.service_name = services.name
            WHERE leads.service_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('service_name');
        });
    }
};
