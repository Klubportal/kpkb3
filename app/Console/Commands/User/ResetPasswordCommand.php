<?php

namespace App\Console\Commands\User;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ResetPasswordCommand extends Command
{
    protected $signature = 'user:reset-password {email? : The email of the user} {--password= : The new password}';

    protected $description = 'Reset a user password (Central/Admin)';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->option('password');

        // Ask for email if not provided
        if (!$email) {
            $email = $this->ask('Email address of the user');
        }

        // Validate email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            $this->error('Invalid email address!');
            return 1;
        }

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found!");
            return 1;
        }

        // Ask for password if not provided
        if (!$password) {
            $password = $this->secret('New password (min. 8 characters)');
            $passwordConfirm = $this->secret('Confirm password');

            if ($password !== $passwordConfirm) {
                $this->error('Passwords do not match!');
                return 1;
            }
        }

        // Validate password
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters!');
            return 1;
        }

        // Update password
        $user->password = Hash::make($password);
        $user->save();

        $this->info("✅ Password successfully reset for: {$user->name} ({$user->email})");

        return 0;
    }
}
