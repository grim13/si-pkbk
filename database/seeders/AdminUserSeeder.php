<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@sipkbk.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('administrator');

        $pekerja_sosial = User::updateOrCreate(
            ['email' => 'pekerjasosial@sipkbk.com'],
            [
                'name' => 'Pekerja Sosial',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $pekerja_sosial->assignRole('pekerja_sosial');

        $kepala_seksi = User::updateOrCreate(
            ['email' => 'kepalaseksi@sipkbk.com'],
            [
                'name' => 'Kepala Seksi',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $kepala_seksi->assignRole('kepala_seksi');
    }
}
