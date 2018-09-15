<?php
namespace Elo\middleware;

use Elo\Controller\AuthCtrl;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use Slim\Http\Request;
use Slim\Http\Response;

class IsLoggedMiddleware
{
	/**
	 * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
	 * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
	 * @param  callable                                 $next     Next middleware
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function __invoke(Request $request, Response $response, $next)
	{
		$session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
		if (AuthCtrl::isLogged($session)) {
			$response = $next($request, $response);
		} else {
			$response = $response->withStatus(405);
		}
		return $response;
	}
}
