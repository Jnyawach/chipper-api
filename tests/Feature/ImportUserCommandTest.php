<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Tests\TestCase;

class ImportUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_users_from_json_url_with_limit()
    {
        $fakeUsers = [
            ["name" => "User One", "email" => "user1@example.com"],
            ["name" => "User Two", "email" => "user2@example.com"],
            ["name" => "User Three", "email" => "user3@example.com"],
        ];
        Http::fake([
            'jsonplaceholder.typicode.com/*' => Http::response($fakeUsers, 200),
        ]);

        $this->assertDatabaseCount('users', 0);
        $exitCode = Artisan::call('import:users https://jsonplaceholder.typicode.com/users 2');
        $this->assertEquals(0, $exitCode);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', [
            'name' => 'User One',
            'email' => 'user1@example.com',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'User Two',
            'email' => 'user2@example.com',
        ]);
    }

    public function test_handles_invalid_url_or_json()
    {
        Http::fake([
            'jsonplaceholder.typicode.com/*' => Http::response('Not Found', 404),
        ]);
        $exitCode = Artisan::call('import:users https://jsonplaceholder.typicode.com/users 2');
        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_handles_invalid_json_structure()
    {
        Http::fake([
            'jsonplaceholder.typicode.com/*' => Http::response('{"foo": "bar"}', 200),
        ]);
        $exitCode = Artisan::call('import:users https://jsonplaceholder.typicode.com/users 2');
        $this->assertEquals(1, $exitCode);
        $this->assertDatabaseCount('users', 0);
    }
}

