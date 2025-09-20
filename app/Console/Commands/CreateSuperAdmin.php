<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:super-admin
                            {--name= : The name of the super admin}
                            {--email= : The email of the super admin}
                            {--password= : The password of the super admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating a new Super Admin user...');

        // Get inputs
        $name = $this->option('name') ?? $this->ask('Enter the super admin name');
        $email = $this->option('email') ?? $this->ask('Enter the super admin email');
        $password = $this->option('password') ?? $this->secret('Enter the super admin password');

        // Validate inputs
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('- ' . $error);
            }
            return Command::FAILURE;
        }

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email '{$email}' already exists!");
            return Command::FAILURE;
        }

        try {
            // Create the super admin user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]);

            $this->info('✅ Super Admin user created successfully!');
            $this->info("📧 Email: {$user->email}");
            $this->info("👤 Name: {$user->name}");
            $this->info("🔑 Role: {$user->role}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to create super admin user: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
