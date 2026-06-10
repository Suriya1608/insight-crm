<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('fbclid', 255)->nullable()->after('source_detail');
            $table->string('utm_campaign', 255)->nullable()->after('fbclid');
            $table->string('utm_medium', 100)->nullable()->after('utm_campaign');
            $table->string('utm_content', 255)->nullable()->after('utm_medium');
            $table->string('utm_term', 255)->nullable()->after('utm_content');
            $table->string('meta_ad_id', 100)->nullable()->after('utm_term');
            $table->string('meta_adset_id', 100)->nullable()->after('meta_ad_id');
            $table->string('meta_campaign_id', 100)->nullable()->after('meta_adset_id');
            $table->string('meta_form_id', 100)->nullable()->after('meta_campaign_id');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'fbclid',
                'utm_campaign',
                'utm_medium',
                'utm_content',
                'utm_term',
                'meta_ad_id',
                'meta_adset_id',
                'meta_campaign_id',
                'meta_form_id',
            ]);
        });
    }
};
