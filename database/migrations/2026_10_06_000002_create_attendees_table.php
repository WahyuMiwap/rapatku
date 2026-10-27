<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->enum('kategori_instansi', ['internal', 'eksternal']);
            $table->string('nama', 100);
            $table->string('divisi', 100)->nullable();
            $table->string('nama_instansi', 150)->nullable();
            $table->string('no_telp', 15);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->boolean('agree_resume')->default(false);
            $table->boolean('agree_daftar_hadir')->default(false);
            $table->string('signature_path');
            $table->timestamps();

            $table->unique(['event_id', 'no_telp']);
            $table->index(['event_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendees');
    }
};
