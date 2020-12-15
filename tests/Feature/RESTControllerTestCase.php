<?php

namespace Tests\Feature;

use App\Currency;
use Illuminate\Support\Facades\Artisan;


class RESTControllerTestCase extends ControllerTestCase
{
    protected $className;
    protected $apiURI;
    protected $modelName;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function init($className, $apiURI, $modelName){
        $this->className = $className; // Sets the api classname.
        $this->apiURI = $apiURI; // Sets the api URI.
        $this->modelName = $modelName; // Sets the model name.
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
     * @param null | string $uri
     * @param int $limit
     * @return void
     */
    public function testIndexMethodUsesRequestLimit($limit = 10, $uri = null){
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
     * @param null | string $uri
     * @param int $limit
     * @return void
     */
    public function testIndexMethodUsesDefaultLimit($limit = 20, $uri = null){
        $uri = ($uri) ?? $this->apiURI; // Use a default URI if a uri isn't specified.
        $response = $this->get($uri); // Get the request response.
        $response->assertStatus(200); // Assert get request is successful.
        $this->assertTrue(count($response->getOriginalContent()) === $limit, "response dump does not have length $limit"); // Assert response has correct array length.
    }

    /**
     * Tests that the index method utilises the _id property in the GET query.
     *
     * @param null | string $uri
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
     * @param null | string $uri
     * @return void
     */
    public function testStoreMethodRequiresBearerToken($uri = null){
        $uri = ($uri) ?? $this->apiURI; // Use a default URI if a uri isn't specified.
        $response = $this->post($this->apiURI); // Send an empty post request to the store endpoint.
        $response->assertStatus(401); // Assert that it returns an error 401 as determined by the middleware.
        $response->assertJson(["message"=>"Unauthenticated."], true); // Assert that it returns an error message as determined by the middleware.
    }

    /**
     * Tests that the store method stores the passed model in the db;
     *
     * @param null | string $uri
     * @return void
     */
    public function testStoreMethodStoresModel($uri = null){
        $uri = ($uri) ?? $this->apiURI; // Use a default URI if a uri isn't specified.
        $newModel = factory($this->className)->make(); // Create a new model according to the classname.
        $token = $this->getAuthenticationToken(); // Get authentication token from test case.

        // Send the post request to store the model.
        $response = $this->post($uri, $newModel->toArray(), [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(201); // Assert the status of the response.
        $response->assertJsonFragment($newModel->toArray()); // Assert that the api response contains the newly created model.
    }

    /**
     * Test that the store method requires a property.
     *
     * @param $property
     * @param null $errorMessage
     * @param string $uri
     * @return void
     */
    protected function storeMethodRequiresPropertyTestCase($property, $errorMessage = null, $uri = null){
        $uri = ($uri) ?? $this->apiURI; // Use a default URI if a uri isn't specified.
        $newModel = factory($this->className)->make(); // Create a new model from the pre-specified classname.
        $token = $this->getAuthenticationToken(); // Get auth token from test case.
        $newModel =  $newModel->toArray(); unset($newModel[$property]); // remove property from newModel created.

        // Send the post request to store the model.
        $response = $this->post($uri, $newModel , [
            'Authorization' => "Bearer $token"
        ]);

        // Make assertions based on the pre-specified request.
        $response->assertJsonFragment(["The given data was invalid."]);
        if(!is_null($errorMessage)) $response->assertJsonFragment([$errorMessage]);
        $response->assertStatus(422);
    }

    /**
     * Test that the update method exists.
     *
     * @param $updateArr
     * @param null | string $uri
     * @return void
     */
    protected function updateMethodEndpointExistsTestCase($updateArr, $uri = null){
        $uri = ($uri) ?? $this->apiURI; // Use a default URI if a uri isn't specified.
        $newModel = factory($this->className)->create(); // Create a new model using the pre-specified classname.
        $token = $this->getAuthenticationToken(); // Get auth token from test case.

        // Send the put request to update the model.
        $response = $this->put($uri.$newModel->id, $updateArr, [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200); // Assert response status is successful.
    }

    /**
     * Test that the update method verifies id of the row being updated.
     *
     * @param $updateArr
     * @param null | string $uri
     * @return void
     */
    public function updateMethodVerifiesIdTestCase($updateArr, $uri = null){
        $uri = ($uri) ?? $this->apiURI; // Use a default URI if a uri isn't specified.
        $newModel = factory($this->className)->create(); // Create a new model and persist in db.
        $token = $this->getAuthenticationToken(); // Get auth token from test case.

        // Send the put request to update the model.
        $response = $this->put($uri.$newModel->id."1", $updateArr, [
            'Authorization' => "Bearer $token"
        ]);

        // Assert the error returned in the request response.
        $response->assertJsonFragment( [$this->modelName." with ID ".$newModel->id."1"." not found"]);
        $response->assertStatus(404);
    }

    /**
     * Test that the update method actually returns an updated model object with the newly specified key-value pair.
     *
     * @param $key
     * @param $value
     * @param null | string $uri
     * @return void
     */
    protected function updateMethodReturnsUpdatedColumnInModelTestCase($key, $value, $uri = null){
        $uri = ($uri) ?? $this->apiURI; // Use a default URI if a uri isn't specified.
        $newModel = factory($this->className)->create(); // Create a new model and persist in db.
        $token = $this->getAuthenticationToken(); // Get auth token from test case.

        // Send the put request to update the model.
        $response = $this->put($uri.$newModel->id, [$key => $value] , [
            'Authorization' => "Bearer $token"
        ]);

        // Assert the column was updated.
        $newModel->{$key} = $value;
        $response->assertJsonFragment($newModel->toArray());
        $response->assertStatus(200);
    }

    /**
     * Tests that the key-value pair used in test case is valid.
     *
     * @param $tcKey
     * @param $tcValue
     * @param null | string $uri
     */
    protected function updateMethodRequiresPropertyToFitTestCase($tcKey, $tcValue, $uri=null){
        $uri = ($uri) ?? $this->apiURI; // Use a default URI if a uri isn't specified.
        $newModel = factory($this->className)->create(); // Create a new model with pre-specified classname.
        $token = $this->getAuthenticationToken(); // Get auth token from test case.

        // Send the put request to update the model.
        $response = $this->put($uri.$newModel->id, [$tcKey => $tcValue] , [
            'Authorization' => "Bearer $token"
        ]);

        // Assert success response.
        $response->assertStatus(200);
        $response->assertJsonFragment($newModel->toArray());

    }

    /**
     *  Tests that the key-value pair required by the test case has an invalid value.
     *
     * @param $tcKey
     * @param $tcInvalidValue
     * @param null | string $errorMsg
     * @param null | string $uri
     */
    protected function updateRequiresPropertyNotToFitTestCase($tcKey, $tcInvalidValue, $errorMsg = null, $uri = null){
        $uri = ($uri) ?? $this->apiURI; // Use a default URI if a uri isn't specified.
        $newModel = factory($this->className)->create(); // Create a new model with pre-specified classname.
        $token = $this->getAuthenticationToken(); // Get auth token from test case.

        // Send the put request to update the model.
        $response = $this->put($uri.$newModel->id, [$tcKey => $tcInvalidValue] , [
            'Authorization' => "Bearer $token"
        ]);

        // Assert error responses.
        $response->assertJsonFragment(["The given data was invalid."]);
        if(!is_null($errorMsg)) $response->assertJsonFragment([$errorMsg]);
        $response->assertStatus(422);
    }


    /**
     * Tests that the destroy endpoint exists.
     * @param null $uri
     */
    protected function destroyMethodEndpointExistsTestCase($uri = null){
        $uri = ($uri) ?? $this->apiURI; // Use a default URI if a uri isn't specified.
        $newModel = factory($this->className)->create(); // Create a new model with pre-specified classname.
        $token = $this->getAuthenticatedAdminToken(); // Get auth token from test case.

        // Send the put request to update the model.
        $response = $this->delete($uri.$newModel->id, [], [
            'Authorization' => "Bearer $token"
        ]);

        $response->assertStatus(200); // Assert successful response.
    }
}
