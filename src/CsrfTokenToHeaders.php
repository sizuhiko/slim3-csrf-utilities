<?php
/**
 * Add Slim Framework 3 CSRF protection middleware datas in response headers
 *
 * @author Aurélien Millet
 * @link https://github.com/aurmil/slim3-csrf-utilities
 * @license https://github.com/aurmil/slim3-csrf-utilities/README.md
 */

namespace Aurmil\Slim;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Csrf\Guard;

class CsrfTokenToHeaders
{
    /**
     * @var Guard
     */
    private $csrf;

    /**
     * @param Guard $csrf
     */
    public function __construct(Guard $csrf)
    {
        $this->csrf = $csrf;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
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
