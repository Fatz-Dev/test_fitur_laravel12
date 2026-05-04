<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('jenjang', ['SD', 'SMP', 'SMA', 'SMK', 'MI', 'MTs', 'MA']);
            $table->text('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->enum('program', ['KPM', 'PPL', 'BOTH'])->default('BOTH');
            $table->unsignedInteger('kuota_kpm')->default(0);
            $table->unsignedInteger('kuota_ppl')->default(0);
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
