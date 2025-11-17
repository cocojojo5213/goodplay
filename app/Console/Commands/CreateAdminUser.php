<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {--email= : The email of the admin user} {--password= : The password for the admin user} {--name= : The name of the admin user}';

    protected $description = 'Create an initial admin user';

    public function handle(): int
    {
        $email = $this->option('email') ?: $this->ask('Email address');
        $password = $this->option('password') ?: $this->secret('Password');
        $name = $this->option('name') ?: $this->ask('Full name');

        if (User::where('email', $email)->exists()) {
            $this->error('A user with that email already exists.');
            return 1;
        }

        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator']
        );

        $user = User::create([
            'email' => $email,
            'password' => Hash::make($password),
            'name' => $name,
        ]);

        $user->roles()->attach($adminRole);

        $this->info('Admin user created successfully!');
        $this->info("Email: {$email}");
        $this->info("Name: {$name}");

        return 0;
    }
}
