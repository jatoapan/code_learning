<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('un usuario puede registrarse correctamente', function () {
    $response = $this->postJson('/api/v1/users', [
        'name' => 'Test User',
        'email' => 'test@prolecom.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(201)
             ->assertJsonStructure(['access_token', 'token_type', 'user']);
    
    $this->assertDatabaseHas('users', ['email' => 'test@prolecom.com']);
});

test('un usuario activo puede hacer login', function () {
    $user = User::factory()->create([
        'email' => 'login@prolecom.com',
        'password' => Hash::make('password123'),
        'status' => 'active'
    ]);

    $response = $this->postJson('/api/v1/sessions', [
        'email' => 'login@prolecom.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['access_token', 'token_type', 'user']);
});

test('un usuario inhabilitado o baneado no puede hacer login', function () {
    $user = User::factory()->create([
        'email' => 'banned@prolecom.com',
        'password' => Hash::make('password123'),
        'status' => 'banned'
    ]);

    $response = $this->postJson('/api/v1/sessions', [
        'email' => 'banned@prolecom.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
             ->assertJsonPath('message', 'Esta cuenta se encuentra inhabilitada.');
});

test('usuario puede cerrar sesión', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test_token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token
    ])->deleteJson('/api/v1/sessions/current');

    $response->assertStatus(200)
             ->assertJsonPath('message', 'Sesión cerrada exitosamente');
});
