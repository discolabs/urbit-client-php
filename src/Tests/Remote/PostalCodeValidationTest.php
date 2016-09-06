<?php

namespace Urbit\Tests\Remote;

class PostalCodeValidationTest extends UrbitClientTest  {

    public function testPostalcodeValidation() {
        $this->assertFalse($this->client->validatePostalCode(1), "Malformed postcode '1' returns false");
        $this->assertFalse($this->client->validatePostalCode(99999), "Invalid postcode '99999' returns false");

        $this->assertTrue($this->client->validatePostalCode('111 44'), "Valid postcode '111 44' returns true");
    }

}
