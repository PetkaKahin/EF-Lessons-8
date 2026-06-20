<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;

describe('API логин', function () {
    it('выдает bearer token', function () {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'secret-password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'secret-password',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'token',
            ]);

        expect($response->json('token'))->toStartWith('Bearer ');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id' => $user->id,
            'name' => 'api-token',
        ]);
    });

    it('не выдает token при неверном пароле', function () {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'secret-password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    });
});
