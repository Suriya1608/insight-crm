<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename courses → services (skip if already done)
        if (Schema::hasTable('courses') && !Schema::hasTable('services')) {
            Schema::rename('courses', 'services');
        }

        // 2. Add service_id to leads (skip if already exists)
        if (Schema::hasTable('leads') && !Schema::hasColumn('leads', 'service_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->unsignedBigInteger('service_id')->nullable()->after('pincode');
                $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();
            });
            DB::statement('UPDATE leads SET service_id = course_id WHERE course_id IS NOT NULL');
        }

        // 3. Drop old foreign keys individually using raw SQL (IF EXISTS avoids errors)
        $dbName = DB::connection()->getDatabaseName();

        $fksToDrop = [
            'leads_course_id_foreign',
            'leads_academic_year_id_foreign',
            'leads_final_course_id_foreign',
        ];

        foreach ($fksToDrop as $fk) {
            $exists = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'leads'
                AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", [$dbName, $fk]);

            if (!empty($exists)) {
                DB::statement("ALTER TABLE `leads` DROP FOREIGN KEY `{$fk}`");
            }
        }

        // 4. Drop the old columns
        Schema::table('leads', function (Blueprint $table) {
            $toDrop = [];
            if (Schema::hasColumn('leads', 'course_id'))        $toDrop[] = 'course_id';
            if (Schema::hasColumn('leads', 'academic_year_id')) $toDrop[] = 'academic_year_id';
            if (Schema::hasColumn('leads', 'quota'))            $toDrop[] = 'quota';
            if (Schema::hasColumn('leads', 'final_course_id'))  $toDrop[] = 'final_course_id';
            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });

        // 5. Drop academic_year_id from campaigns
        if (Schema::hasTable('campaigns') && Schema::hasColumn('campaigns', 'academic_year_id')) {
            $fkCamp = 'campaigns_academic_year_id_foreign';
            $campFkExists = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'campaigns'
                AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", [$dbName, $fkCamp]);
            if (!empty($campFkExists)) {
                DB::statement("ALTER TABLE `campaigns` DROP FOREIGN KEY `{$fkCamp}`");
            }
            Schema::table('campaigns', fn (Blueprint $t) => $t->dropColumn('academic_year_id'));
        }

        // 6. Drop education-only tables
        Schema::dropIfExists('course_intakes');
        Schema::dropIfExists('course_manager_assignments');
        Schema::dropIfExists('academic_years');
    }

    public function down(): void
    {
        if (Schema::hasTable('services') && !Schema::hasTable('courses')) {
            Schema::rename('services', 'courses');
        }

        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'course_id')) {
                $table->unsignedBigInteger('course_id')->nullable();
            }
            if (!Schema::hasColumn('leads', 'academic_year_id')) {
                $table->unsignedBigInteger('academic_year_id')->nullable();
            }
            if (!Schema::hasColumn('leads', 'quota')) {
                $table->string('quota', 20)->nullable();
            }
            if (!Schema::hasColumn('leads', 'final_course_id')) {
                $table->unsignedBigInteger('final_course_id')->nullable();
            }
            if (Schema::hasColumn('leads', 'service_id')) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            }
        });
    }
};
