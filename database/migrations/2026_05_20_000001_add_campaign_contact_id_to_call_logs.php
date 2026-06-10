<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_contact_id')->nullable()->after('lead_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->dropIndex(['campaign_contact_id']);
            $table->dropColumn('campaign_contact_id');
        });
    }
};
