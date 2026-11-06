<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap', 150);
            $table->string('jabatan', 150);
            $table->string('divisi', 100);
            $table->string('jenis_kelamin', 10)->default('L');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['nama_lengkap', 'is_active']);
            $table->index('divisi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

