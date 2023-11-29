<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@local.com';

        $user = User::factory()->create([
            'name' => 'Administrator',
            'email' => $email,
            'password' => Hash::make('admin@123')
        ]);

        DB::statement("UPDATE `{$user->getTable()}` SET `id` = 0 WHERE `email` = '{$email}'");
    }
}
