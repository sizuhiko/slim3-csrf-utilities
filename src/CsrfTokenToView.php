<?php

namespace Aurmil\Slim;

use ArrayAccess;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Csrf\Guard;

class CsrfTokenToView
{
    private $csrf;
    private $renderer;

    public function __construct(Guard $csrf, $renderer)
    {
        $this->csrf = $csrf;
        $this->renderer = $renderer;
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
            if ($this->renderer instanceof ArrayAccess) {
                $this->renderer['csrf_token'] = $csrfToken;
            } elseif (method_exists($this->renderer, 'addAttribute')) {
                $this->renderer->addAttribute('csrf_token', $csrfToken);
            } else {
                throw new \UnexpectedValueException('Unsupported view renderer type.');
            }
        }

        return $next($request, $response);
    }
}
