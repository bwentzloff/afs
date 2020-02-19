<?php

namespace Tests\Feature\Http\Controllers;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Login endpoint.
     *
     * Verifies the given email and password exist
     * for a user in the DB and returns Auth token.
     *
     * @return void
     */
    public function testLogin()
    {
        $user = factory(User::class, 'user')->create(
            ['password' => bcrypt($password = 'some_password')]
        );
        $this->assertDatabaseHas('users', $user->toArray());

        $response = $this
            ->actingAs($user)
            ->json('POST', '/api/v1/auth/login',
            [
                'email' => $user->email,
                'password' => $password
            ]
        );
        $response
            ->assertStatus(200)
            ->assertSee('success')
            ->assertSee('token');
    }

    /**
     * Test ResetPassword endpoint.
     *
     * Matches given token and email address to
     * password reset reservation from DB.
     *
     * @return void
     */
    public function testResetPassword()
    {
        $user = factory(User::class, 'user')->create(
            ['password' => bcrypt($password = 'some_password')]
        );
        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => 'some_token',
            'created_at' => Carbon::now()
        ]);
        $original_hash = User::where('email', $user->email)
            ->first()->password;
        $this->assertDatabaseHas('users',
            ['password' => $original_hash]
        );

        $response = $this
            ->actingAs($user)
            ->json('POST', '/api/v1/user/resetPassword',
            [
                'email' => $user->email,
                'password' => 'some_other_password',
                'token' => 'some_token'
            ]
        );
        $response->assertStatus(200);
        // user still exists
        $this->assertDatabaseHas('users', $user->toArray());
        // password hash has changed
        $this->assertDatabaseMissing('users',
            ['password' => $original_hash]
        );
    }
}
