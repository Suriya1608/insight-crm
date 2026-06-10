<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Expand enum to include both old and new values
        DB::statement("ALTER TABLE campaign_contacts MODIFY status ENUM(
            'pending','called','interested','not_interested','no_answer','callback','converted',
            'new','assigned','contacted','follow_up','lost'
        ) NOT NULL DEFAULT 'new'");

        // Step 2: Migrate existing data to lead status equivalents
        DB::statement("UPDATE campaign_contacts SET status = 'new'      WHERE status = 'pending'");
        DB::statement("UPDATE campaign_contacts SET status = 'contacted' WHERE status IN ('called','no_answer')");
        DB::statement("UPDATE campaign_contacts SET status = 'follow_up' WHERE status = 'callback'");

        // Step 3: Finalize to lead statuses only
        DB::statement("ALTER TABLE campaign_contacts MODIFY status ENUM(
            'new','assigned','contacted','interested','not_interested','converted','follow_up','lost'
        ) NOT NULL DEFAULT 'new'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE campaign_contacts MODIFY status ENUM(
            'pending','called','interested','not_interested','no_answer','callback','converted',
            'new','assigned','contacted','follow_up','lost'
        ) NOT NULL DEFAULT 'pending'");

        DB::statement("UPDATE campaign_contacts SET status = 'pending'  WHERE status = 'new'");
        DB::statement("UPDATE campaign_contacts SET status = 'called'   WHERE status = 'contacted'");
        DB::statement("UPDATE campaign_contacts SET status = 'callback' WHERE status = 'follow_up'");

        DB::statement("ALTER TABLE campaign_contacts MODIFY status ENUM(
            'pending','called','interested','not_interested','no_answer','callback','converted'
        ) NOT NULL DEFAULT 'pending'");
    }
};
