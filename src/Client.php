<?php
namespace Urbit;

use GuzzleHttp\Psr7;
use Urbit\Auth\UWA;

class Client
{
    /** @var string */
    private $storeKey;
    /** @var string */
    private $sharedSecret;
    /** @var bool */
    private $stage;

    /** @var string */
    public $baseUrl;

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
        $this->baseUrl = $this->stage ? Constants::STAGE_BASE_URL : Constants::PROD_BASE_URL;

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
            'base_uri' => $this->baseUrl,
            'timeout'  => 15.0,
            'connect_timeout' => 15.0
        ]);

        $uwa = new UWA(
            $this->storeKey,
            $this->sharedSecret,
            $method,
            $this->baseUrl . $uri,
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

    /**
     * @param string $pickupLocationId location UUID string
     * @param string|\DateTime $from
     * @param string|\DateTime $to
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getUrbningHours($pickupLocationId = null, $from = null, $to = null)
    {
        // Process the From / To inputs
        if (null === $from) {
            $from = date('Y-m-d');
        } else if ($from instanceof \DateTime) {
            $from = $from->format('Y-m-d');
        } else {
            self::validateInput('/^\d{4}-\d{2}-\d{2}$/', $from, 'To parameter must be in YYYY-MM-DD format.');
        }

        if (null === $to) {
            $to = date('Y-m-d');
        } else if ($to instanceof \DateTime) {
            $to = $to->format('Y-m-d');
        } else {
            self::validateInput('/^\d{4}-\d{2}-\d{2}$/', $to, 'To parameter must be in YYYY-MM-DD format.');
        }

        if ($from > $to) {
            throw new InvalidInputException('From cannot be greater than To.');
        }

        if (!$pickupLocationId) {
            throw new InvalidInputException('A pickup location ID must be provided.');
        }

        $response = $this->request('GET', 'stores/me/pickup-locations/urbning-hours', [
            'query' => [
                'from' => $from,
                'to' => $to,
                'pickup_location_id' => $pickupLocationId,
            ],
        ]);

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
     * @return bool         True if the postcode is valid, false if an invalid postalcode (e.g. "asdf") or
     *                      if the supplied postal code is not included in the urb-it area
     * @throws \Exception   An exception is thrown if there was a problem communicating with the API
     */
    public function validatePostalCode($postalCode)
    {
        try {
            // validate the given postalcode
            self::validateInput('/^[\d]{3}\s?[\d]{2}$/', $postalCode, 'Invalid postal code.');

            // Check the postalcode with the api
            $response = $this->request('POST', 'postalcode/validate', [
                'json' => [
                    'postal_code' => str_replace(' ', '', $postalCode),
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (InvalidInputException $exception) {
            return false;
        } catch (ClientException $exception) {
            // The client returns a 404 for an invalid postalcode
            if (404 === $exception->getResponse()->getStatusCode()) {
                return false;
            }

            throw $exception;
        }
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
                throw new InvalidInputException($errorMessage);
            }
        }
    }
}
