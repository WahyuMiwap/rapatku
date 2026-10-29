<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun superadmin default via .env (register publik tidak disediakan).
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@rapatku.local')],
            [
                'name' => 'Superadmin',
                'role' => 'superadmin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'admin123')),
            ]
        );

        $defaultSignatories = [
            [
                'nama_lengkap' => 'Kombes Pol. Heru Prasetyo, S.I.K., M.Hum.',
                'jabatan' => 'Kepala BNN Kota Surabaya',
                'jenis_identitas' => 'NRP',
                'nomor_identitas' => '75020380',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'Kasubag Umum BNN Kota Surabaya',
                'jabatan' => 'Kasubag Umum',
                'jenis_identitas' => 'NIP',
                'nomor_identitas' => '19800720 200604 2 008',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'Katim Pencegahan & Dayamas (P2M)',
                'jabatan' => 'Katim P2M',
                'jenis_identitas' => 'NIP',
                'nomor_identitas' => '19820315 200801 1 005',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'Katim Rehabilitasi BNN Kota Surabaya',
                'jabatan' => 'Katim Rehabilitasi',
                'jenis_identitas' => 'NIP',
                'nomor_identitas' => '19850910 201001 2 012',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'Katim Pemberantasan & Intelijen',
                'jabatan' => 'Katim Pemberantasan',
                'jenis_identitas' => 'NIP',
                'nomor_identitas' => '19780418 200312 1 002',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'Ketua Panitia Pelaksana',
                'jabatan' => 'Ketua Panitia',
                'jenis_identitas' => 'NIP',
                'nomor_identitas' => '....................................................',
                'is_active' => true,
            ],
        ];

        foreach ($defaultSignatories as $sig) {
            \App\Models\Signatory::firstOrCreate(
                ['nama_lengkap' => $sig['nama_lengkap']],
                $sig
            );
        }
    }
}
