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

        $defaultEmployees = [
            [
                'nama_lengkap' => 'Kombes Pol. Heru Prasetyo, S.I.K., M.Hum.',
                'jabatan' => 'Kepala BNN Kota Surabaya',
                'divisi' => 'Sub Bagian Umum',
                'jenis_kelamin' => 'L',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'Kasubbag Umum BNN Kota Surabaya',
                'jabatan' => 'Kasubbag Umum',
                'divisi' => 'Sub Bagian Umum',
                'jenis_kelamin' => 'L',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'Moch. Roano Ardiansyah',
                'jabatan' => 'Staf Pelaksana P2M',
                'divisi' => 'P2M',
                'jenis_kelamin' => 'L',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'Ahmad Fauzi, S.H.',
                'jabatan' => 'Katim Pemberantasan',
                'divisi' => 'Brantas',
                'jenis_kelamin' => 'L',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'dr. Siti Rahmawati',
                'jabatan' => 'Katim Rehabilitasi',
                'divisi' => 'Rehabilitasi',
                'jenis_kelamin' => 'P',
                'is_active' => true,
            ],
            [
                'nama_lengkap' => 'Budi Santoso, S.Sos.',
                'jabatan' => 'Katim P2M',
                'divisi' => 'P2M',
                'jenis_kelamin' => 'L',
                'is_active' => true,
            ],
        ];

        foreach ($defaultEmployees as $emp) {
            \App\Models\Employee::firstOrCreate(
                ['nama_lengkap' => $emp['nama_lengkap']],
                $emp
            );
        }
    }
}
