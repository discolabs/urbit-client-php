<?php
namespace Urbit;

use GuzzleHttp\Psr7;
use Urbit\Auth\UWA;

class Client
{
    private $storeKey;
    private $sharedSecret;
    private $stage;
    private $baseUri;

    /**
     * Urbit constructor.
     *
     * @param string $storeKey
     * @param string $sharedSecret
     * @param bool   $stage
     *
     * @throws \RuntimeException
     */
    public function __construct(
        $storeKey = '',
        $sharedSecret = '',
        $stage = false
    ) {
        $this->storeKey = (string) $storeKey;
        $this->sharedSecret = (string) $sharedSecret;
        $this->stage = (bool) $stage;
        $this->baseUri = $this->stage ? Constants::STAGE_BASE_URL : Constants::PROD_BASE_URL;

        if (!$this->storeKey) {
            throw new \RuntimeException('Store key is missing.');
        }

        if (!$this->sharedSecret) {
            throw new \RuntimeException('Shared secret is missing.');
        }
    }

    /**
     * Make a request to the Urb-it API
     *
     * @param string $method
     * @param null   $uri
     * @param array  $options
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function request($method = 'GET', $uri = null, $options = [])
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->baseUri,
            'timeout'  => 15.0,
            'connect_timeout' => 15.0
        ]);

        $uwa = new UWA(
            $this->storeKey,
            $this->sharedSecret,
            $method,
            $this->baseUri . $uri,
            isset($options->json) ? $options->json : ''
        );

        $options = [
            'headers' => [
                'Authorization' => $uwa->getAuthorizationHeader()
            ]
        ];

        $response = $client->request($method, $uri, $options);

        if ($response->getStatusCode() !== 200) {
            $body = $response->getBody();

            if ($body && isset($body->developer_message)) {
                throw new \Exception($body->developer_message);
            } else {
                throw new \Exception('HTTP ' . $response->getStatusCode());
            }
        }

        return $response;
    }

    public function getUrbningHours($from = '', $to = '', $pickupLocationId = null)
    {
        self::validateInput(
            '/^[\d]{4}-[\d]{2}-[\d]{2}$/',
            [$from, $to],
            'From and To parameters must be in YYYY-MM-DD format.'
        );

        if ($from > $to) {
            throw new \Exception('From cannot be greater than To.');
        }

        if (!isset($pickupLocationId)) {
            throw new \Exception('A pickup location ID must be provided.');
        }

        $response = $this->request(
            'GET',
            'openinghours',
            ['query' => ['from' => $from, 'to' => $to, 'pickup_location_id' => $pickupLocationId]]
        );

        return $response->getBody();
    }

    /**
     * Get opening hours
     *
     * @param string $from
     * @param string $to
     *
     * @return mixed
     * @throws \Exception
     */
    public function getOpeningHours($from = '', $to = '')
    {
        self::validateInput(
            '/^[\d]{4}-[\d]{2}-[\d]{2}$/',
            [$from, $to],
            'From and To parameters must be in YYYY-MM-DD format.'
        );

        if ($from > $to) {
            throw new \Exception('From cannot be greater than To.');
        }

        $response = $this->request('GET', 'openinghours', ['query' => ['from' => $from, 'to' => $to]]);

        return $response->getBody();
    }

    /**
     * Validate postal code
     *
     * @param string $postalCode
     *
     * @return bool
     * @throws \Exception
     */
    public function validatePostalCode($postalCode = '')
    {
        self::validateInput('/^[\d]{3}\s?[\d]{2}$/', $postalCode, 'Invalid postal code.');

        $response = $this->request(
            'POST',
            'postalcode/validate',
            ['json' => ['postal_code' => str_replace(' ', '', $postalCode)]]
        );

        return $response->getStatusCode() === 200;
    }

    /**
     * Validate delivery
     *
     * @param array $order
     *
     * @return mixed
     * @throws \Exception
     */
    public function validateDelivery($order = [])
    {
        $response = $this->request('POST', 'delivery/validate', ['json' => $order]);

        return $response->getBody();
    }

    /**
     * Create order
     *
     * @param array $order
     *
     * @return mixed
     * @throws \Exception
     */
    public function createOrder($order = [])
    {
        $response = $this->request('POST', 'order', ['json' => $order]);

        return $response;
    }

    /**
     * Validate input params using regex
     *
     * @param $regex
     * @param $inputs
     * @param $errorMessage
     *
     * @throws \Exception
     */
    private static function validateInput($regex, $inputs, $errorMessage)
    {
        if (!is_array($inputs)) {
            $inputs = array($inputs);
        }

        foreach ($inputs as $input) {
            if (!preg_match($regex, $input)) {
                throw new \Exception($errorMessage);
            }
        }
    }
}
