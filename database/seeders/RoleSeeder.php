<?php

namespace Database\Seeders;

use App\Modules\Role\Domain\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'name' => 'admin'],
            ['id' => 2, 'name' => 'project-manager'],
            ['id' => 3, 'name' => 'country-manager'],
        ];

        foreach ($roles as $role) {
            \DB::table('role')->updateOrInsert(
                ['id' => $role['id']],
                array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }
}
