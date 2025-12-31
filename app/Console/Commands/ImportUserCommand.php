<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class ImportUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:users {url} {limit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from a public JSON file URL';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');
        $limit = (int) $this->argument('limit');

        $this->info("Fetching users from: $url");
        $response = Http::get($url);
        if (!$response->ok()) {
            $this->error('Failed to fetch users.');
            return 1;
        }

        $users = $response->json();

        if (!is_array($users) || !isset($users[0]) || !is_array($users[0])) {
            $this->error('Invalid JSON structure.');
            return 1;
        }

        $imported = 0;
        foreach (array_slice($users, 0, $limit) as $userData) {

            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                ]
            );
            $imported++;
        }
        $this->info("Imported $imported users.");
        return 0;
    }
}
