<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ControllerTestCase extends TestCase
{

    use RefreshDatabase;
    private $authenticatedUserBearerToken;
    private $authenticatedAdminBearerToken;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call("passport:install"); // Sets up laravel passport.
        $this->registerTestAdmin(); // Creates an admin to test the access control routes.
        $this->loginTestAdmin(); // Tries to login admin.
        $this->registerTestUser(); // Creates a user to test the authenticated routes.
        $this->loginTestUser(); // Tries to login user.
    }

    protected function tearDown(): void
    {
        $this->logoutTestUser(); // Logs out test user
        $this->logoutTestAdmin(); // Logs out test admin
        parent::tearDown();
    }

    /**
     * Returns dump with headers and session.
     * @param TestResponse $response
     *
     * @return void
     */
    protected function superDump(TestResponse $response){
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
     * Creates a new admin to be used for authentication during testing.
     *
     * @return void
     */
    private function registerTestAdmin(){
        (factory(User::class)->make([
            'email'=> 'admin@doe.com',
            'type' => 1
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
     * Logs out the test user and resets the authentication token.
     *
     * @return void
     */
    private function logoutTestUser(){
        $token = $this->getAuthenticationToken();
        $response = $this->json('POST', '/api/logout', [], [
            'Authorization' => "Bearer $token"
        ]);
        $this->authenticatedUserBearerToken = null;
    }

    /**
     * Logs in a test admin and sets the authentication token.
     *
     * @return void
     */
    private function loginTestAdmin(){
        $response = $this->json('POST', '/api/login', ["email" => "admin@doe.com", 'password' => 'password']);
        $this->authenticatedAdminBearerToken = ($response->getOriginalContent())["token"];
    }

    /**
     * Logs out the test admin and resets the authentication token.
     *
     * @return void
     */
    private function logoutTestAdmin(){
        $token = $this->getAuthenticatedAdminToken();
        $response = $this->json('POST', '/api/logout', [], [
            'Authorization' => "Bearer $token"
        ]);
        $this->authenticatedAdminBearerToken = null;
    }

    /**
     * Returns the authentication token of the logged in user.
     * @return string
     */
    protected function getAuthenticationToken(){
        return $this->authenticatedUserBearerToken;
    }

    /**
     * Returns the authentication token of the logged in admin.
     * @return string
     */
    protected function getAuthenticatedAdminToken(){
        return $this->authenticatedAdminBearerToken;
    }
}
