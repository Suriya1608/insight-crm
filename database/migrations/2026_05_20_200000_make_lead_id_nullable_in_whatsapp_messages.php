<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Disable FK checks so we can modify the column regardless of constraint state
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('ALTER TABLE `whatsapp_messages` MODIFY `lead_id` BIGINT UNSIGNED NULL');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('ALTER TABLE `whatsapp_messages` MODIFY `lead_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
