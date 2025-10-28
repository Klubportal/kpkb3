<?php

namespace App\Console\Commands;

use App\Models\Central\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetUserPassword extends Command
{
    protected $signature = 'user:password {email} {password}';
    protected $description = 'Set user password';

    public function handle()
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (!$user) {
            $this->error('User not found!');
            return 1;
        }

        $user->password = Hash::make($this->argument('password'));
        $user->save();

        $this->info('Password updated successfully!');
        return 0;
    }
}
