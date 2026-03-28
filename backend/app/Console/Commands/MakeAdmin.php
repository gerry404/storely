<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'storely:make-admin {email} {--role=superadmin : Role to assign (admin or superadmin)}';
    protected $description = 'Promote a user to admin or superadmin';

    public function handle(): int
    {
        $email = $this->argument('email');
        $role = $this->option('role');

        if (!in_array($role, ['admin', 'superadmin'])) {
            $this->error("Invalid role: {$role}. Use 'admin' or 'superadmin'.");
            return 1;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        $user->update(['role' => $role]);

        $this->info("User '{$user->name}' ({$email}) is now a {$role}.");

        return 0;
    }
}
