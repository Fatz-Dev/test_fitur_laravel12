<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mahasiswa_profiles', function (Blueprint $table) {
            // KPM = desa saja, PPL = sekolah saja, PKPPM = keduanya (desa + sekolah berdekatan)
            $table->string('program_choice')->default('PKPPM')->after('nim');
        });
    }

    public function down(): void
    {
        Schema::table('mahasiswa_profiles', function (Blueprint $table) {
            $table->dropColumn('program_choice');
        });
    }
};
