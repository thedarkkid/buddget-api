<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ControllerTest extends TestCase
{

    use RefreshDatabase;

    private $authenticatedUserBearerToken;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call("passport:install"); // Sets up laravel passport.
        $this->registerTestUser(); // Creates a user to test the authenticated routes.
        $this->loginTestUser(); // Tries to login user.
    }

    /**
     * Returns dump with headers and session.
     * @param TestResponse $response
     *
     * @return void
     */
    private function superDump(TestResponse $response){
        $response->dumpHeaders();
        $response->dumpSession();
        $response->dump();
    }

    /**
     * Creates a new user to be used for authentication during testing.
     *
     * @return void
     */
    private function registerTestUser(){
        (factory(User::class)->make([
            'email'=> 'john@doe.com'
        ]))->save();
    }

    /**
     * Logs in a test user and sets the authentication token.
     *
     * @return void
     */
    private function loginTestUser(){
        $response = $this->json('POST', '/api/login', ["email" => "john@doe.com", 'password' => 'password']);
        $this->authenticatedUserBearerToken = ($response->getOriginalContent())["token"];
    }

    /**
     * A basic test example to stop phpunit warning.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Returns the authentication token of the logged in user.
     * @return string
     */
    protected function getAuthenticationToken(){
        return $this->authenticatedUserBearerToken;
    }

}
