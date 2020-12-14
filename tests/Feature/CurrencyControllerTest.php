<?php

namespace Tests\Feature;

use App\Currency;
use Illuminate\Support\Facades\Artisan;


class CurrencyControllerTest extends ControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
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

    /**
     * Tests that the index method utilises the name property in the GET query.
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

    /**
     * Tests that the index method utilises the _id property in the GET query.
     *
     * @return void
     */
    public function testIndexMethodUsesIdQueryProperty(){
        $newCurrency = factory(Currency::class)->create();
        $response = $this->get('/api/currencies?_id='.$newCurrency->id);
        $response->assertStatus(200);
        $this->assertTrue(count($response->getOriginalContent()) === 1, "response does not return result utilizing id property.");
        $response->assertJsonFragment($newCurrency->toArray());
    }

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

    /**
     * Tests that the store method stores the passed currency in the db;
     *
     * @return void
     */
    public function testStoreMethodStoresCurrency(){
        $nCurrency = factory(Currency::class)->make();
        $token = $this->getAuthenticationToken();
        $response = $this->post('/api/currencies', $nCurrency->toArray(), [
            'Authorization' => "Bearer $token"
        ]);
        $response->assertStatus(201);
        $response->assertJsonFragment($nCurrency->toArray());
    }

    /**
     * Test that the store method requires the name property.
     *
     * @return void
     */
    public function testStoreMethodRequiresNameProperty(){
        $nCurrency = factory(Currency::class)->make();
        $token = $this->getAuthenticationToken();
        $nCurrencyArr =  $nCurrency->toArray(); unset($nCurrencyArr["name"]);
        $response = $this->post('/api/currencies', $nCurrencyArr , [
            'Authorization' => "Bearer $token"
        ]);
        $response->assertJsonFragment(["The given data was invalid."]);
        $response->assertJsonFragment(["The name field is required."]);
        $response->assertStatus(422);
    }

    /**
     * Test that the store method requires the acronym property.
     *
     * @return void
     */
    public function testStoreMethodRequiresAcronymProperty(){
        $nCurrency = factory(Currency::class)->make();
        $token = $this->getAuthenticationToken();
        $nCurrencyArr =  $nCurrency->toArray(); unset($nCurrencyArr["acronym"]);
        $response = $this->post('/api/currencies', $nCurrencyArr , [
            'Authorization' => "Bearer $token"
        ]);
        $response->assertJsonFragment(["The given data was invalid."]);
        $response->assertJsonFragment(["The acronym field is required."]);
        $response->assertStatus(422);
    }

    /**
     * Test that the store method does not accept an acronym property
     * with more than three characters.
     *
     * @return void
     */
    public function testStoreMethodRequiresAcronymPropertyToBeMaxOfThreeCharacters(){
        $nCurrency = factory(Currency::class)->make(["acronym" => "NIMMNI"]);
        $token = $this->getAuthenticationToken();
        $response = $this->post('/api/currencies', $nCurrency->toArray() , [
            'Authorization' => "Bearer $token"
        ]);
        $response->assertJsonFragment(["The given data was invalid."]);
        $response->assertJsonFragment(["The acronym must be 3 characters."]);
        $response->assertStatus(422);
    }

    /**
     * Test that the store method does not accept an acronym property
     * with less than three characters.
     *
     * @return void
     */
    public function testStoreMethodRequiresAcronymPropertyToNotBeLessThanThreeCharacters(){
        $nCurrency = factory(Currency::class)->make(["acronym" => "NI"]);
        $token = $this->getAuthenticationToken();
        $response = $this->post('/api/currencies', $nCurrency->toArray() , [
            'Authorization' => "Bearer $token"
        ]);
        $response->assertJsonFragment(["The given data was invalid."]);
        $response->assertJsonFragment(["The acronym must be 3 characters."]);
        $response->assertStatus(422);
    }

    /**
     * Test that the update method exists.
     *
     * @return void
     */
    public function testUpdateMethodEndpointExists(){
        $nCurrency = factory(Currency::class)->create();
        $token = $this->getAuthenticationToken();
        $response = $this->put('/api/currencies/'.$nCurrency->id, ["name" => "Baghdad Nadir"] , [
            'Authorization' => "Bearer $token"
        ]);
        $response->assertStatus(200);
    }

    /**
     * Test that the update method verifies id of the row being updated.
     *
     * @return void
     */
    public function testUpdateMethodVerifiesId(){
        $nCurrency = factory(Currency::class)->create();
        $token = $this->getAuthenticationToken();
        $response = $this->put('/api/currencies/'.$nCurrency->id."1", ["name" => "Baghdad Nadir"] , [
            'Authorization' => "Bearer $token"
        ]);
        $response->assertJsonFragment( ["Currency with ID ".$nCurrency->id."1"." not found"]);
        $response->assertStatus(404);
    }

    /**
     * Test that the update method actually returns an updated currency
     * object with new name.
     *
     * @return void
     */
    public function testUpdateMethodReturnsUpdatedNameInCurrency(){
        $nCurrency = factory(Currency::class)->create();
        $token = $this->getAuthenticationToken();
        $response = $this->put('/api/currencies/'.$nCurrency->id, ["name" => "Baghdad Nadir"] , [
            'Authorization' => "Bearer $token"
        ]);
        $nCurrency->name = "Baghdad Nadir";
        $response->assertJsonFragment($nCurrency->toArray());
        $response->assertStatus(200);
    }

    /**
     * Test that the update method actually returns an updated currency
     * object with new acronym.
     *
     * @return void
     */
    public function testUpdateMethodReturnsUpdatedAcronymInCurrency(){
        $nCurrency = factory(Currency::class)->create();
        $token = $this->getAuthenticationToken();
        $response = $this->put('/api/currencies/'.$nCurrency->id, ["acronym" => "BNB"] , [
            'Authorization' => "Bearer $token"
        ]);
        $nCurrency->acronym = "BNB";
        $response->assertJsonFragment($nCurrency->toArray());
        $response->assertStatus(200);
    }

    /**
     * Test that the update method does not accept an acronym property
     * with more than three characters.
     *
     * @return void
     */
    public function testUpdateMethodRequiresAcronymPropertyToBeMaxOfThreeCharacters(){
        $nCurrency = factory(Currency::class)->create();
        $token = $this->getAuthenticationToken();
        $response = $this->put('/api/currencies/'.$nCurrency->id, ["acronym" => "BNBMI"] , [
            'Authorization' => "Bearer $token"
        ]);
        $response->assertJsonFragment(["The given data was invalid."]);
        $response->assertJsonFragment(["The acronym must be 3 characters."]);
        $response->assertStatus(422);
    }

    /**
     * Test that the update method does not accept an acronym property
     * with less than three characters.
     *
     * @return void
     */
    public function testUpdateMethodRequiresAcronymPropertyToNotBeLessThanThreeCharacters(){
        $nCurrency = factory(Currency::class)->create();
        $token = $this->getAuthenticationToken();
        $response = $this->put('/api/currencies/'.$nCurrency->id, ["acronym" => "BI"] , [
            'Authorization' => "Bearer $token"
        ]);
        $response->assertJsonFragment(["The given data was invalid."]);
        $response->assertJsonFragment(["The acronym must be 3 characters."]);
        $response->assertStatus(422);
    }

    public function testDestroyMethodEndpointExists(){
        $nCurrency = factory(Currency::class)->create();
        $token = $this->getAuthenticationToken();
        $response = $this->delete('/api/currencies/'.$nCurrency->id, [], [
            'Authorization' => "Bearer $token"
        ]);
        $response->dump();
        $response->assertStatus(200);
    }
}
