<?php

namespace Tests\Feature;

use App\Currency;
use Illuminate\Support\Facades\Artisan;


class RESTControllerTestCase extends ControllerTestCase
{
    protected $className;
    protected $apiURI;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function init($className, $apiURI){
        $this->className = $className; // Sets the api classname
        $this->apiURI = $apiURI; // Sets the api URI
    }

    /**
     * Generates a list of models and dumps it in the in-memory DB.
     * A laravel 'seeder' for the class($className) has to have been created before this can work.
     *
     * @param int $row_amount
     *
     * @return void
     */
    protected function seedDB($row_amount = 20){
       factory($this->className, $row_amount)->create(); // Generates models using laravel factories.
    }

    /**
     * Tests that the index method in controller class utilises the _limit field.
     *
     * @param null $uri
     * @param int $limit
     * @return void
     */
    public function testIndexMethodUsesRequestLimit($uri = null, $limit = 10){
        $uri = ($uri) ?? $this->apiURI; // Use a default URI if a uri isn't specified.
        $response = $this->get($uri.'?_limit='.$limit); // Get the request response.
        $response->assertStatus(200); // Assert get request is successful.

        $this->assertFalse(count($response->getOriginalContent()) === ($limit+10), "response dump has length ".($limit+10)); // Assert response does not have wrong array length.
        $this->assertTrue(count($response->getOriginalContent()) === $limit, "response dump does not have length $limit"); // Assert response has correct array length.
    }

    /**
     * Tests that the index method in the default class ($className) utilises
     * the default limit if the limit field is not filled.
     *
     * @param null $uri
     * @param int $limit
     * @return void
     */
    public function testIndexMethodUsesDefaultLimit($uri = null, $limit = 20){
        $uri = ($uri) ?? $this->apiURI; // Use a default URI if a uri isn't specified.
        $response = $this->get($uri); // Get the request response.
        $response->assertStatus(200); // Assert get request is successful.
        $this->assertTrue(count($response->getOriginalContent()) === $limit, "response dump does not have length $limit"); // Assert response has correct array length.
    }

    /**
     * Tests that the index method utilises the _id property in the GET query.
     *
     * @param null $uri
     * @return void
     */
    public function testIndexMethodUsesIdQueryProperty($uri = null){
        $uri = ($uri) ?? $this->apiURI; // Use a default URI if a uri isn't specified.
        $newModel = factory($this->className)->create(); // Create a new model for use in test.
        $response = $this->get($uri.'?_id='.$newModel->id); // Make a GET request utilizing the model id to get the model.

        $response->assertStatus(200); // Assert request is successful.
        $this->assertTrue(count($response->getOriginalContent()) === 1, "response does not return result utilizing id property.");
        $response->assertJsonFragment($newModel->toArray()); // Assert that the newly created model was called using its id.
    }

    /**
     * Test that the store method needs the user to be unauthenticated.
     *
     * @return void
     */
    public function testStoreMethodRequiresBearerToken(){
        $response = $this->post($this->apiURI);
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
        $token = $this->getAuthenticatedAdminToken();
        $response = $this->delete('/api/currencies/'.$nCurrency->id, [], [
            'Authorization' => "Bearer $token"
        ]);
        $response->assertStatus(200);
    }
}
