<?php
/**
 * Pass Slim Framework 3 CSRF protection middleware datas to view
 *
 * @author Aurélien Millet
 * @link https://github.com/aurmil/slim3-csrf-utilities
 * @license https://github.com/aurmil/slim3-csrf-utilities/blob/master/LICENSE.md
 */

namespace Aurmil\Slim;

use ArrayAccess;
use UnexpectedValueException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Csrf\Guard;

class CsrfTokenToView
{
    /**
     * @var Guard
     */
    private $csrf;

    private $renderer;

    /**
     * @param Guard $csrf
     * @param $renderer
     */
    public function __construct(Guard $csrf, $renderer)
    {
        $this->csrf = $csrf;
        $this->renderer = $renderer;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     * @throws UnexpectedValueException
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
            // waiting for a possible Slim View Interface
            if ($this->renderer instanceof ArrayAccess) {
                $this->renderer['csrf_token'] = $csrfToken;
            } elseif (method_exists($this->renderer, 'addAttribute')) {
                $this->renderer->addAttribute('csrf_token', $csrfToken);
            } else {
                throw new UnexpectedValueException('Unsupported view renderer type.');
            }
        }

        return $next($request, $response);
    }
}
