<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Agency;

class AgencyTest extends TestCase
{
    public function test_get_name_returns_correct_name()
    {
        $agency = new Agency('prueba de agencia', 'http://prueba.com' , false);
        $this->assertNotEmpty($agency->getName());
    }

    public function test_validate_name_returns_false_for_short_name()
    {
        $agency = new Agency('', 'http://prueba.com', false);
        $this->assertEmpty($agency->getName());
    }
    public function test_validate_url_returns_false_for_not_valid_url()
    {
        $agency = new Agency('prueba de agencia', 'http://prueba.com', false);

        $this->assertFalse($agency->getUrl() != filter_var($agency->getUrl(), FILTER_VALIDATE_URL));
    }   
    public function test_get_name_returns_correct_name5(){
          $agency = new Agency('prueba de agencia', 'http://prueba.com' , true);
        $this->assertTrue($agency->compareIsApproved(true));
    }
    public function test_get_name_returns_correct_name6(){
          $agency = new Agency('prueba de agencia', 'http://prueba.com' , "falso");
        $this->assertFalse($agency->compareIsApproved(true));
    }
    public function test_get_name_returns_correct_name7(){
        $agency = new Agency('prueba de agencia', 'http://prueba.com' , "false");
        $this->assertTrue($agency->compareIsApproved(false));
    }

    
    

}
