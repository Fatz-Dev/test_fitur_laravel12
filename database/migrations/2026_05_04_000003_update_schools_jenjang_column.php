<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL: drop the enum check constraint, then change to plain varchar
        DB::statement('ALTER TABLE schools DROP CONSTRAINT IF EXISTS schools_jenjang_check');
        DB::statement('ALTER TABLE schools ALTER COLUMN jenjang TYPE VARCHAR(50)');
        DB::statement('ALTER TABLE schools ALTER COLUMN jenjang DROP NOT NULL');
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->enum('jenjang', ['SD','SMP','SMA','SMK','MI','MTs','MA'])->nullable(false)->change();
        });
    }
};
