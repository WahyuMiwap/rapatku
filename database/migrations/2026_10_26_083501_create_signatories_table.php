<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signatories', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap', 150);
            $table->string('jabatan', 150);
            $table->enum('jenis_identitas', ['NIP', 'NRP'])->default('NIP');
            $table->string('nomor_identitas', 100);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatories');
    }
};
