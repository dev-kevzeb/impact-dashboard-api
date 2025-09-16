<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
      public function validateNameTest1(){
        $country = \App\Models\Country::at("Argentina");
        $this->assertTrue($country->validateName(), "El name no debe ir vacio");
    }

     public function validateNameTest2(){
        $country = \App\Models\Country::at("");
        $this->assertFalse($country->validateName(), "El name no debe ir vacio");
    }


    // public function test_the_application_returns_a_successful_response(): void
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }
}
