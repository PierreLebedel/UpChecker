<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    protected $signature = 'app:create-user';

    protected $description = 'Create new user';

    public function handle(): void
    {
        do {
            $details  = $this->askForUserDetails($details ?? null);

            $name     = $details['name'];
            $email    = $details['email'];
            $password = $details['password'];
            $locale   = $details['locale'];

        } while (! $this->confirm("Create user {$name} <{$email}>?", true));

        $user = User::forceCreate([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
            'locale'   => $locale,
        ]);

        $this->info("Created new user #{$user->id}");
    }

    protected function askForUserDetails($defaults = null): array
    {
        $name     = $this->ask("Please enter user's name", $defaults['name'] ?? null);
        $email    = $this->askUniqueEmail("Please enter user's email", $defaults['email'] ?? null);
        $password = $this->ask("Please enter user's password (will be visible)", $defaults['password'] ?? null);
        $locale   = $this->choice("Please select user's locale", config('app.locales'), $defaults['locale'] ?? null);

        return compact('name', 'email', 'password', 'locale');
    }

    protected function askUniqueEmail($message, $default = null): string
    {
        do {
            $email = $this->ask($message, $default);
        } while (! $this->checkEmailIsValid($email) || ! $this->checkEmailIsUnique($email));

        return $email;
    }

    protected function checkEmailIsValid($email): bool
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Sorry, "'.$email.'" is not a valid email address!');

            return false;
        }

        return true;
    }

    public function checkEmailIsUnique($email): bool
    {
        if ($existingUser = User::whereEmail($email)->first()) {
            $this->error('Sorry, "'.$existingUser->email.'" is already in use by '.$existingUser->name.'!');

            return false;
        }

        return true;
    }
}
