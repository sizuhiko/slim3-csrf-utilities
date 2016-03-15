<?php

namespace Aurmil\Slim;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Csrf\Guard;

class CsrfTokenToHeaders
{
    private $csrf;

    public function __construct(Guard $csrf)
    {
        $this->csrf = $csrf;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $csrfToken = [
            $nameKey  => $request->getAttribute($nameKey),
            $valueKey => $request->getAttribute($valueKey)
        ];

        if ($csrfToken[$nameKey] && $csrfToken[$valueKey]) {
            $response = $response->withHeader('X-CSRF-Token', json_encode($csrfToken));
        }

        return $next($request, $response);
    }
}
