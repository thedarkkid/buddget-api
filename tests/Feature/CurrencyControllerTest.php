<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CurrencyControllerTest extends TestCase
{

   private function getCurrencyStoreRequestStub(){

    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Tests that the index method in currency controller class utilises the _limit field.
     */
    public function testCurrencyControllerIndexMethodUsesLimit(){

    }
}
