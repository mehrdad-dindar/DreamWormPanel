<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::create([
           "name" => "super_admin",
           "guard_name" => "web",
        ]);

        $role->syncPermissions(Permission::all());
        Role::create([
            "name" => "customer",
            "guard_name" => "web",
        ]);
    }
}
