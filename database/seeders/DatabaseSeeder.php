<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => '123456',
            'created_at' => now(), 'updated_at' => now()
        ]);
        User::factory()->create([
            'name' => 'Test Client',
            'email' => 'test@admin.com',
            'password' => '123456',
            'created_at' => now(), 'updated_at' => now()
        ]);

        DB::table('companies')->insert([
            ['name' => 'Main Company', 'slug' => 'main-company', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('company_user')->insert([
            ['company_id' => '1', 'user_id' => '1','created_at' => now(), 'updated_at' => now()],
            ['company_id' => '1', 'user_id' => '2','created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('roles')->insert([
            ['name' => 'Super Admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Panel User', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('role_user')->insert([
            ['user_id' => '1', 'role_id' => '1','created_at' => now(), 'updated_at' => now()],
            ['user_id' => '2', 'role_id' => '2','created_at' => now(), 'updated_at' => now()],
        ]);

    }

}
