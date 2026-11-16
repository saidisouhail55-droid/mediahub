<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = config('services.admin.name');
        $email = config('services.admin.email');
        $password = config('services.admin.password');

        if (
            ! is_string($name)
            || trim($name) === ''
            || ! is_string($email)
            || filter_var($email, FILTER_VALIDATE_EMAIL) === false
            || ! is_string($password)
            || $password === ''
        ) {
            throw new RuntimeException('Configure ADMIN_NAME, ADMIN_EMAIL, and ADMIN_PASSWORD before running AdminSeeder.');
        }

        User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password],
        );
    }
}
