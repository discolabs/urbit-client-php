<?php

namespace Urbit\Auth;

use Ramsey\Uuid\Uuid;

class UWA
{
    private $header;

    /**
     * UWA constructor
     *
     * @param string $storeKey
     * @param string $sharedSecret
     * @param string $method
     * @param string $url
     * @param string $json
     * @param int    $timestamp
     */
    public function __construct(
        $storeKey = '',
        $sharedSecret = '',
        $method = '',
        $url = '',
        $json = '',
        $timestamp = null
    ) {
        $this->setAuthorizationHeader(
            (string) $storeKey,
            (string) $sharedSecret,
            (string) $method,
            (string) $url,
            (string) $json,
            (int) $timestamp
        );
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
     * @param $storeKey
     * @param $method
     * @param $url
     * @param $timestamp
     * @param $nonce
     * @param $digest
     *
     * @return mixed
     */
    private function getMessageToSign($storeKey, $method, $url, $timestamp, $nonce, $digest)
    {
        return implode('', array(
            $storeKey,
            strtoupper($method),
            strtolower($url),
            $timestamp,
            $nonce,
            $digest
        ));
    }

    /**
     * Return the digest
     *
     * @param string $json
     *
     * @return mixed
     */
    private function getDigest($json = '')
    {
        return $json && strlen($json) > 0 ? base64_encode(md5($json, true)) : '';
    }

    /**
     * Return the secret key
     *
     * @param $sharedSecret
     *
     * @return mixed
     */
    private function getSecretKey($sharedSecret)
    {
        return base64_decode($sharedSecret);
    }

    /**
     * Set the UWA header
     *
     * @param string $storeKey
     * @param string $sharedSecret
     * @param string $method
     * @param string $url
     * @param string $json
     * @param int    $timestamp
     */
    private function setAuthorizationHeader(
        $storeKey = '',
        $sharedSecret = '',
        $method = '',
        $url = '',
        $json = '',
        $timestamp = null
    ) {
        $timestamp = $timestamp ?: time(); // Get current Unix timestamp
        $nonce = Uuid::uuid4(); // Generate RFC4122 v4 compliant UUID
        $json = utf8_encode($json); // Ensure JSON content is encoded a UTF-8

        $secretKey = $this->getSecretKey($sharedSecret);
        $digest = $this->getDigest($json);
        $messageToSign = $this->getMessageToSign($storeKey, $method, $url, $timestamp, $nonce, $digest);
        $signature = $this->getSignature($secretKey, $messageToSign);

        // Return header
        $this->header = 'UWA ' . implode(':', array($storeKey, $signature, $nonce, $timestamp));
    }

    /**
     * Return the UWA header
     *
     * @return string
     */
    public function getAuthorizationHeader()
    {
        return $this->header;
    }
}
