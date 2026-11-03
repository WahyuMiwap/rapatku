<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('nama_mengetahui', 150)->nullable()->after('category');
            $table->string('jabatan_mengetahui', 150)->nullable()->after('nama_mengetahui');
            $table->string('nip_mengetahui', 100)->nullable()->after('jabatan_mengetahui');

            $table->string('nama_pembuat', 150)->nullable()->after('nip_mengetahui');
            $table->string('jabatan_pembuat', 150)->nullable()->after('nama_pembuat');
            $table->string('nip_pembuat', 100)->nullable()->after('jabatan_pembuat');
        });

        // Salin data penandatangan lama ke mengetahui jika ada
        if (Schema::hasColumn('events', 'nama_penandatangan')) {
            DB::table('events')
                ->whereNotNull('nama_penandatangan')
                ->update([
                    'nama_mengetahui' => DB::raw('nama_penandatangan'),
                    'jabatan_mengetahui' => DB::raw('jabatan_penandatangan'),
                    'nip_mengetahui' => DB::raw('nip_penandatangan'),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'nama_mengetahui', 'jabatan_mengetahui', 'nip_mengetahui',
                'nama_pembuat', 'jabatan_pembuat', 'nip_pembuat',
            ]);
        });
    }
};
