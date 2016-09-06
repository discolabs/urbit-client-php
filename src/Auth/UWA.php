<?php

namespace Urbit\Auth;

use Psr\Http\Message\RequestInterface;
use Ramsey\Uuid\Uuid;

class UWA implements UWAProviderInterface
{
    /** @var string */
    private $storeKey;
    /** @var string */
    private $sharedSecret;

    /**
     * UWA constructor
     *
     * @param string $storeKey
     * @param string $sharedSecret
     */
    public function __construct($storeKey = '', $sharedSecret = '') {
        $this->storeKey = $storeKey;
        $this->sharedSecret = $sharedSecret;
    }

    /**
     * @param RequestInterface $request
     * @param string $nonce
     * @param int $timestamp
     * @return string
     */
    public function getRequestAuthorizationHeader(RequestInterface $request, $nonce = null, $timestamp = null) {
        $timestamp = $timestamp ?: time(); // Get current Unix timestamp
        $nonce = $nonce ?: Uuid::uuid4(); // Generate RFC4122 v4 compliant UUID

        $secretKey = $this->getSecretKey();
        $messageToSign = $this->getMessageToSign($request, $nonce, $timestamp);
        $signature = $this->getSignature($secretKey, $messageToSign);

        // Return header
        return 'UWA ' . implode(':', [$this->storeKey, $signature, $nonce, $timestamp]);
    }

    /**
     * Return the message to sign
     *
     * @param $secretKey
     * @param $messageToSign
     *
     * @return mixed
     */
    private function getSignature($secretKey, $messageToSign)
    {
        return base64_encode(hash_hmac('sha256', utf8_encode($messageToSign), $secretKey, true));
    }

    /**
     * Return the message to sign
     *
     * @param RequestInterface $request
     * @param string $nonce
     * @param int $timestamp
     *
     * @return mixed
     */
    private function getMessageToSign(RequestInterface $request, $nonce, $timestamp)
    {
        $digest = $this->getDigest($request->getBody()->getContents());

        return implode('', [
            $this->storeKey,
            strtoupper($request->getMethod()),
            strtolower($request->getUri()),
            $timestamp,
            $nonce,
            $digest,
        ]);
    }

    /**
     * Return the digest
     *
     * @param string $json
     *
     * @return string
     */
    private function getDigest($json = '')
    {
        return $json && strlen($json) > 0 ? base64_encode(md5(utf8_encode($json), true)) : '';
    }

    /**
     * Return the secret key
     *
     * @return string
     */
    private function getSecretKey()
    {
        return base64_decode($this->sharedSecret);
    }

}
