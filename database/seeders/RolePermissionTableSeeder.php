<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'administrator',
            'wali',
            'pekerja_sosial',
            'kepala_seksi',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $manageAccessPermission = Permission::firstOrCreate(['name' => 'access.manage']);
        $manageWaliPermission = Permission::firstOrCreate(['name' => 'wali.manage']);
        $managePendaftaranPermission = Permission::firstOrCreate(['name' => 'pendaftaran.manage']);

        $adminRole = Role::where('name', 'administrator')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($manageAccessPermission);
            $adminRole->givePermissionTo($manageWaliPermission);
            $adminRole->givePermissionTo($managePendaftaranPermission);
        }

        $pendaftaranPermission = Permission::firstOrCreate(['name' => 'pendaftaran.form']);
        $waliRole = Role::where('name', 'wali')->first();
        if ($waliRole) {
            $waliRole->givePermissionTo($pendaftaranPermission);
        }

        $asesmenPermission = Permission::firstOrCreate(['name' => 'asesmen.do']);
        $pekerjaSosialRole = Role::where('name', 'pekerja_sosial')->first();
        if ($pekerjaSosialRole) {
            $pekerjaSosialRole->givePermissionTo($asesmenPermission);
        }
    }
}
