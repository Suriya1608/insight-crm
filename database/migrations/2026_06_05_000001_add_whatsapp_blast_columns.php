<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->enum('wa_blast_status', ['idle', 'queued', 'sending', 'completed', 'failed'])->default('idle')->after('status');
            $table->unsignedInteger('wa_sent_count')->default(0)->after('wa_blast_status');
            $table->unsignedInteger('wa_failed_count')->default(0)->after('wa_sent_count');
            $table->timestamp('wa_last_blast_at')->nullable()->after('wa_failed_count');
        });

        Schema::table('campaign_contacts', function (Blueprint $table) {
            $table->enum('wa_status', ['pending', 'sent', 'failed'])->default('pending')->after('call_count');
            $table->timestamp('wa_sent_at')->nullable()->after('wa_status');
            $table->text('wa_error')->nullable()->after('wa_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['wa_blast_status', 'wa_sent_count', 'wa_failed_count', 'wa_last_blast_at']);
        });

        Schema::table('campaign_contacts', function (Blueprint $table) {
            $table->dropColumn(['wa_status', 'wa_sent_at', 'wa_error']);
        });
    }
};
