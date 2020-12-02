<?php

namespace Tests\Feature;

use App\Currency;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CurrencyControllerTest extends ControllerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call("passport:install"); // Sets up laravel passport.
        $this->seedDB();  // Seeds the DB with data.
    }

    /**
     * Generates a List of currencies.
     * @param int $row_amount
     *
     * @return void
     */
    private function seedDB($row_amount = 20){
       factory(Currency::class, $row_amount)->create();
    }

    /**
     * Tests that the index method in currency controller class utilises
     * the _limit field.
     *
     * @return void
     */
    public function testIndexMethodUsesRequestLimit(){
        $response = $this->get('/api/currencies?_limit=10');
        $response->assertStatus(200);
        $this->assertFalse(count($response->getOriginalContent()) === 20, "response dump has length 20");
        $this->assertTrue(count($response->getOriginalContent()) === 10, "response dump does not have length 10");
    }

    /**
     * Tests that the index method in the currency class utilises
     * the default limit if the limit field is not filled.
     *
     * @return void
     */
    public function testIndexMethodUsesDefaultLimit(){
        $response = $this->get('/api/currencies');
        $response->assertStatus(200);
        $this->assertTrue(count($response->getOriginalContent()) === 20, "response dump does not have length 20");
    }

    // TODO: test that it utilises the acronym property
    /**
     * Tests that the index method utilises the name property in a query.
     *
     * @return void
     */
    public function testIndexMethodUsesNameQueryProperty(){
        $newCurrency = factory(Currency::class)->make([
            'name'=> 'Algerian Naira'
        ]);
        $newCurrency->save();

        $response = $this->get('/api/currencies?name=algerian+naira');

        $response->assertStatus(200);
        $this->assertTrue(count($response->getOriginalContent()) === 1, "response does not return result utilizing name property.");
        $response->assertJson(["data" => [$newCurrency->toArray()]]);
    }

    // TODO: test the validation, and length of the acronym property.
    /**
     * Test that the store method needs the user to be unauthenticated.
     *
     * @return void
     */
    public function testStoreMethodRequiresBearerToken(){
        $response = $this->post('/api/currencies');
        $response->assertJson(["message"=>"Unauthenticated."], true);
        $response->assertStatus(401);
    }
}
