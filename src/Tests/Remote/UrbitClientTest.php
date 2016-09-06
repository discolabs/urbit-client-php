<?php

namespace Urbit\Tests\Remote;

use PHPUnit\Framework\TestCase;
use Urbit\Client;

abstract class UrbitClientTest extends TestCase {

    /** @var Client */
    protected $client;

    /**
     * Setup each test
     */
    protected function setUp() {
        parent::setUp();

        $this->createClient();
    }

    /**
     * Tear down after each test
     */
    protected function tearDown() {
        parent::tearDown();

        $this->client = null;
    }

    /**
     * Initialise the Urbit client
     */
    protected function createClient() {
        static $config = null;

        if (null === $config) {
            $config = parse_ini_file(__DIR__ . '/../../../credentials.ini');
            if (is_readable($localConfig = __DIR__ . '/../../../credentials.local.ini')) {
                $config = array_merge($config, parse_ini_file($localConfig));
            }
        }

        $storeKey = isset($config['store_key']) ? (string)$config['store_key'] : null;
        $sharedSecret = isset($config['shared_secret']) ? (string)$config['shared_secret'] : null;
        $stage = !empty($config['stage']);

        $this->client = new Client($storeKey, $sharedSecret, $stage);
    }

}
