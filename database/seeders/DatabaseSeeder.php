<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    private $permissions = [
        'create',
        'update',
        'read',
        'delete'
    ];

    public function run(): void
    {
        foreach ($this->permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $adminRole = Role::create(['name' => 'Admin']);
        $surveyorRole = Role::create(['name' => 'Surveyor']);
        $verifikatorRole = Role::create(['name' => 'Verifikator']);

        $allPermissions = Permission::pluck('id', 'id')->all();
        $adminRole->syncPermissions($allPermissions);

        $surveyorPermissions = Permission::whereIn('name', ['create', 'read'])->pluck('id')->all();
        $surveyorRole->syncPermissions($surveyorPermissions);

        $verifikatorPermissions = Permission::whereIn('name', ['update', 'read'])->pluck('id')->all();
        $verifikatorRole->syncPermissions($verifikatorPermissions);

        $superAdminUser = User::create([
            'nama' => 'Admin',
            'email' => 'admin@mailinator.com',
            'password' => Hash::make('password')
        ]);
        $superAdminUser->assignRole($adminRole);

        $surveyorUser = User::create([
            'nama' => 'Surveyor',
            'email' => 'surveyor@mailinator.com',
            'password' => Hash::make('password')
        ]);
        $surveyorUser->assignRole($surveyorRole);

        $verifikatorUser = User::create([
            'nama' => 'Verifikator',
            'email' => 'verifikator@mailinator.com',
            'password' => Hash::make('password')
        ]);
        $verifikatorUser->assignRole($verifikatorRole);
    }
}
