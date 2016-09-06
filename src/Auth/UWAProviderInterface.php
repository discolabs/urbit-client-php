<?php

namespace Urbit\Auth;

use Psr\Http\Message\RequestInterface;

interface UWAProviderInterface {

    /**
     * Return the UWA header
     *
     * @param RequestInterface $request
     * @param string $nonce
     * @param int $timestamp
     * @return string
     */
    public function getRequestAuthorizationHeader(RequestInterface $request, $nonce = null, $timestamp = null);

}
