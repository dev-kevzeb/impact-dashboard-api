<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CountryTest extends TestCase
{
    /**
     * A basic feature test example.
     */
      public function validateNameTest1(){
        $country = \App\Models\Country::at("Argentina");
        $this->assertTrue($country->validateName(), "El name no debe ir vacio");
    }

     public function validateNameTest2(){
        $country = \App\Models\Country::at("");
        $this->assertFalse($country->validateName(), "El name no debe ir vacio");
    }


    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
