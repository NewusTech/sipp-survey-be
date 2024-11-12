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
        // Create permissions if they don't already exist
        foreach ($this->permissions as $permission) {
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
            }
        }

        // Create roles if they don't already exist
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $surveyorRole = Role::firstOrCreate(['name' => 'Surveyor']);
        $verifikatorRole = Role::firstOrCreate(['name' => 'Verifikator']);

        // Sync all permissions to the admin role
        $allPermissions = Permission::pluck('id', 'id')->all();
        $adminRole->syncPermissions($allPermissions);

        // Sync specific permissions to the surveyor role
        $surveyorPermissions = Permission::whereIn('name', ['create', 'read'])->pluck('id')->all();
        $surveyorRole->syncPermissions($surveyorPermissions);

        // Sync specific permissions to the verifikator role
        $verifikatorPermissions = Permission::whereIn('name', ['update', 'read'])->pluck('id')->all();
        $verifikatorRole->syncPermissions($verifikatorPermissions);

        // Create or update users and assign roles
        $this->createUser('Admin', 'admin@mailinator.com', 'password', $adminRole);
        $this->createUser('Surveyor', 'surveyor@mailinator.com', 'password', $surveyorRole);
        $this->createUser('Verifikator', 'verifikator@mailinator.com', 'password', $verifikatorRole);
    }

    /**
     * Create or update a user.
     *
     * @param string $name
     * @param string $email
     * @param string $password
     * @param \Spatie\Permission\Models\Role $role
     * @return void
     */
    private function createUser(string $name, string $email, string $password, $role)
    {
        $user = User::firstOrCreate(
            ['email' => $email], // Check if the user already exists by email
            [
                'nama' => $name,
                'password' => Hash::make($password),
            ]
        );

        // Assign the role to the user
        $user->assignRole($role);
    }
}
