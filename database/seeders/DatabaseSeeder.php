<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserStateSeeder::class,
            ProgramStateSeeder::class,
            ProjectStateSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            TestUsersSeeder::class,
        ]);

        $this->resetSequences();

        $this->command->info('Database seeding completed.');
        $this->command->info('Admin: admin@pacific.com / admin123');
    }

    private function resetSequences(): void
    {
        if (config('database.default') !== 'pgsql') {
            return;
        }

        // Solo tablas con IDs fijos en seeders. Las demás se resetean automáticamente con migrate:fresh
        $tables = ['user_state', 'role', 'user', 'permission'];

        foreach ($tables as $table) {
            try {
                $maxId = \DB::table($table)->max('id');
                if (!$maxId) continue;

                $sequenceName = "{$table}_seq";
                if (empty(\DB::select("SELECT 1 FROM pg_sequences WHERE sequencename = ?", [$sequenceName]))) {
                    $sequenceName = "{$table}_id_seq";
                }

                \DB::statement("SELECT setval('{$sequenceName}', {$maxId}, true)");
            } catch (\Exception $e) {
                continue;
            }
        }
    }
}
