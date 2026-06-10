<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_contacts', function (Blueprint $table) {
            $table->enum('quota', ['management', 'counselling'])->nullable()->after('status');
            $table->unsignedBigInteger('converted_course_id')->nullable()->after('quota');
            $table->foreign('converted_course_id')->references('id')->on('courses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('campaign_contacts', function (Blueprint $table) {
            $table->dropForeign(['converted_course_id']);
            $table->dropColumn(['quota', 'converted_course_id']);
        });
    }
};
