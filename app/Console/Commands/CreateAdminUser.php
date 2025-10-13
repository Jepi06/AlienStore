<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create 
                            {--email=admin@alienstore.com : Admin email address}
                            {--password=admin123 : Admin password}
                            {--name=Administrator : Admin name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a default admin user for AlienStore';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        // Validate input
        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
            'name' => $name,
        ], [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error('  • ' . $error);
            }
            return Command::FAILURE;
        }

        // Check if admin user already exists
        $existingAdmin = DB::table('sec_user')
            ->where('email', $email)
            ->first();

        if ($existingAdmin) {
            $this->warn('Admin user with email "' . $email . '" already exists!');
            return Command::SUCCESS;
        }

        // Get Admin role
        $adminRole = DB::table('sec_role')
            ->where('name', 'Admin')
            ->first();

        if (!$adminRole) {
            $this->error('Admin role not found. Please run: php artisan db:seed --class=SecRoleSeeder');
            return Command::FAILURE;
        }

        // Create admin user
        try {
            DB::table('sec_user')->insert([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info('✅ Admin user created successfully!');
            $this->table(['Field', 'Value'], [
                ['Name', $name],
                ['Email', $email],
                ['Password', $password],
                ['Role', 'Admin'],
            ]);
            $this->warn('⚠️  Please change the password after first login!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to create admin user: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
