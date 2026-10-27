<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('nama_penandatangan')->nullable()->after('category');
            $table->string('jabatan_penandatangan')->nullable()->after('nama_penandatangan');
            $table->string('nip_penandatangan')->nullable()->after('jabatan_penandatangan');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['nama_penandatangan', 'jabatan_penandatangan', 'nip_penandatangan']);
        });
    }
};
