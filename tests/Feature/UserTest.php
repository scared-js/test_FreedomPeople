<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_login()
    {
        User::factory(1)->create();
        $user = User::all()->first();

        $this->json('POST', '/api/user/login', ['email' => $user->email,'password' => '12345678'])
            ->assertJson([
                'success' => true,
            ]);

        $this->json('POST', '/api/user/login', ['email' => $user->email,'password' => '123'])
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_save()
    {
        User::factory(1)->create();
        $user = User::all()->first();

        $this->json('POST', '/api/user/save', ['name' => 'test'])
            ->assertJson([
                'success' => false,
            ]);

        $response = $this->json('POST', '/api/user/login', ['email' => $user->email,'password' => '12345678']);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . json_decode($response->content())->token,
            'Accept' => 'application/json',
        ])->json('POST', '/api/user/save', ['name' => 'test'])
            ->assertJson([
                'success' => true,
            ]);

    }

    public function test_search()
    {
        User::factory(1)->create();
        $user = User::all()->first();
        $user->role = User::role_admin;
        $user->save();

        $response = $this->json('POST', '/api/user/login', ['email' => $user->email,'password' => '12345678']);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . json_decode($response->content())->token,
            'Accept' => 'application/json',
        ])->json('POST', '/api/user/search')
            ->assertJson([
                'success' => true,
            ]);
    }
}
