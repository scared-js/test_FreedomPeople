<?php

namespace Tests\Feature;

use App\Models\Car;
use App\Models\PivotUserCar;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CarTest extends TestCase
{
    use RefreshDatabase;

    public function test_search()
    {
        User::factory(1)->create();
        Car::factory(10)->create();
        $user = User::all()->first();

        $response = $this->json('POST', '/api/user/login', ['email' => $user->email,'password' => '12345678']);

        $response_car = $this->withHeaders([
            'Authorization' => 'Bearer ' . json_decode($response->content())->token,
            'Accept' => 'application/json',
        ])->json('POST', '/api/car/search', ['email' => $user->email,'password' => '123'])
            ->assertJson([
                'success' => true,
            ]);

        $this->assertTrue(count(json_decode($response_car->content())->cars) === 10);
    }

    public function test_save()
    {
        User::factory(1)->create();
        Car::factory(1)->create();
        $user = User::all()->first();
        $car = Car::all()->first();
        $user->role = User::role_admin;
        $user->save();

        $response = $this->json('POST', '/api/user/login', ['email' => $user->email,'password' => '12345678']);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . json_decode($response->content())->token,
            'Accept' => 'application/json',
        ])->json('POST', '/api/car/save', ['id' => $car->id,'name' => 'test'])
            ->assertJson([
                'success' => true,
            ]);

        $car_new = Car::all()->first();
        $this->assertTrue($car_new->name === 'test');
    }

    public function test_delete()
    {
        User::factory(1)->create();
        Car::factory(1)->create();
        $user = User::all()->first();
        $car = Car::all()->first();
        $user->role = User::role_admin;
        $user->save();

        $response = $this->json('POST', '/api/user/login', ['email' => $user->email,'password' => '12345678']);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . json_decode($response->content())->token,
            'Accept' => 'application/json',
        ])->json('POST', '/api/car/delete', ['id' => $car->id])
            ->assertJson([
                'success' => true,
            ]);

        $cars = Car::all();
        $this->assertTrue($cars->count() === 0);
    }

    public function test_assign()
    {
        User::factory(5)->create();
        Car::factory(5)->create();
        $user = User::all()->first();
        $car = Car::all()->first();

        $response = $this->json('POST', '/api/user/login', ['email' => $user->email,'password' => '12345678']);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . json_decode($response->content())->token,
            'Accept' => 'application/json',
        ])->json('POST', '/api/car/assign', ['car_id' => $car->id])
            ->assertJson([
                'success' => true,
            ]);

        $pivot = PivotUserCar::all()->first();
        $this->assertTrue($pivot->status === 1 && $pivot->user_id === $user->id && $pivot->car_id === $car->id);

        $cars = Car::all();
        $this->withHeaders([
            'Authorization' => 'Bearer ' . json_decode($response->content())->token,
            'Accept' => 'application/json',
        ])->json('POST', '/api/car/assign', ['car_id' => $cars->get(1)->id])
            ->assertJson([
                'message' => 'У вас уже есть арендованая машина',
            ]);

        $user = User::all()->get(1);
        $response = $this->json('POST', '/api/user/login', ['email' => $user->email,'password' => '12345678']);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . json_decode($response->content())->token,
            'Accept' => 'application/json',
        ])->json('POST', '/api/car/assign', ['car_id' => $car->id])
            ->assertJson([
                'message' => 'У машины есть активные пользователи',
            ]);
    }
}
