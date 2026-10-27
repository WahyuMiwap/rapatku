<?php

use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom uuid nullable terlebih dahulu
        if (! Schema::hasColumn('events', 'uuid')) {
            Schema::table('events', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });
        }

        if (! Schema::hasColumn('attendees', 'uuid')) {
            Schema::table('attendees', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->after('id');
            });
        }

        // 2. Isi data yang sudah ada dengan UUID v7
        foreach (Event::whereNull('uuid')->cursor() as $event) {
            $event->updateQuietly(['uuid' => (string) Str::uuid7()]);
        }

        foreach (Attendee::whereNull('uuid')->cursor() as $attendee) {
            $attendee->updateQuietly(['uuid' => (string) Str::uuid7()]);
        }

        // 3. Tambahkan unique index
        Schema::table('events', function (Blueprint $table) {
            $table->unique('uuid');
        });

        Schema::table('attendees', function (Blueprint $table) {
            $table->unique('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });

        Schema::table('attendees', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropColumn('uuid');
        });
    }
};
