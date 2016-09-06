<?php

namespace Urbit\Request;

use Urbit\Auth\UWAProviderInterface;

class Middleware {

    /**
     * @param UWAProviderInterface $UWAProvider
     * @return \Closure
     */
    public static function uwaAuth(UWAProviderInterface $UWAProvider) {
        return function (callable $handler) use ($UWAProvider) {
            return new UwaAuthMiddleware($handler, $UWAProvider);
        };
    }

}
