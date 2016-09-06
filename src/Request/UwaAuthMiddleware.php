<?php

namespace Urbit\Request;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Urbit\Auth\UWAProviderInterface;

class UwaAuthMiddleware {

    /** @var callable */
    private $nextHandler;
    /** @var UWAProviderInterface */
    private $UWAProvider;

    /**
     * @param callable $nextHandler Next handler to invoke.
     * @param UWAProviderInterface $UWAProvider
     */
    public function __construct(callable $nextHandler, UWAProviderInterface $UWAProvider)
    {
        $this->nextHandler = $nextHandler;
        $this->UWAProvider = $UWAProvider;
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return PromiseInterface
     */
    public function __invoke(RequestInterface $request, array $options)
    {
        $modify = [
            'set_headers' => [
                'Authorization' => $this->UWAProvider->getRequestAuthorizationHeader($request),
            ],
        ];

        $fn = $this->nextHandler;
        return $fn(\GuzzleHttp\Psr7\modify_request($request, $modify), $options);
    }

}
