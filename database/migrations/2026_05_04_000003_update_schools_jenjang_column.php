<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite does not support ALTER COLUMN on enum types.
        // We recreate the table with jenjang as a nullable string.
        Schema::table('schools', function (Blueprint $table) {
            $table->string('jenjang')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->enum('jenjang', ['SD','SMP','SMA','SMK','MI','MTs','MA'])->nullable(false)->change();
        });
    }
};
