<?php

namespace Tests\Feature;

use App\Currency;
use Illuminate\Support\Facades\Artisan;


class CurrencyControllerTest extends RESTControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->init(Currency::class, '/api/currencies/', "Currency"); // Initialises test case.
        $this->seedDB();  // Seeds the DB with data.
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
     * Test that the store method requires the name property.
     *
     * @return void
     */
    public function testStoreMethodRequiresNameProperty(){
        $this->storeMethodRequiresPropertyTestCase("name", "The name field is required.");
    }

    /**
     * Test that the store method requires the acronym property.
     *
     * @return void
     */
    public function testStoreMethodRequiresAcronymProperty(){
        $this->storeMethodRequiresPropertyTestCase("acronym", "The acronym field is required.");
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
        $this->updateMethodEndpointExistsTestCase(["name" => "Baghdad Nadir"]);
    }


    /**
     * Test that the update method verifies id of the row being updated.
     *
     * @return void
     */
    public function testUpdateMethodVerifiesId(){
        $this->updateMethodVerifiesIdTestCase(["name" => "Baghdad Nadir"]);
    }

    /**
     * Test that the update method actually returns an updated currency
     * object with new name.
     *
     * @return void
     */
    public function testUpdateMethodReturnsUpdatedNameInCurrency(){
        $this->updateMethodReturnsUpdatedColumnInModelTestCase("name", "Baghdad Nadir");
    }

    /**
     * Test that the update method actually returns an updated currency
     * object with new acronym.
     *
     * @return void
     */
    public function testUpdateMethodReturnsUpdatedAcronymInCurrency(){
        $this->updateMethodReturnsUpdatedColumnInModelTestCase("acronym", "BNB");
    }

    /**
     * Test that the update method does not accept an acronym property
     * with more than three characters.
     *
     * @return void
     */
    public function testUpdateMethodRequiresAcronymPropertyToBeMaxOfThreeCharacters(){
        $this->updateRequiresPropertyNotToFitTestCase("acronym", "BNBMI", "The acronym must be 3 characters.");
    }

    /**
     * Test that the update method does not accept an acronym property
     * with less than three characters.
     *
     * @return void
     */
    public function testUpdateMethodRequiresAcronymPropertyToNotBeLessThanThreeCharacters(){
        $this->updateRequiresPropertyNotToFitTestCase("acronym", "BI", "The acronym must be 3 characters.");
    }


    /**
     * Tests that the destroy method exists.
     *
     */
    public function testDestroyMethodEndpointExists(){
        $this->destroyMethodEndpointExistsTestCase();
    }
}
